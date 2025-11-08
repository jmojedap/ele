<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

class Gemini_client {

    /**
     * Funciones para solicitar respuestas la API de Gemini
     * Versión 2025-10-02
    */
    
    /**
     * Recibe mensaje de usuario, genera respuesta y guarda los mensajes
     * @param array $request_settings :: Detalles de los requerimientos de la respuesta
     * @return array $data :: Array con los detalles de la respuesta generada
     * 2025-08-14
     */
    function generate($request_settings)
    {
        // Solicitar respuesta a la API de Gemini
        $request_settings['model_id'] = $request_settings['model'] ?? 'gemini-2.0-flash-lite';
        $request_settings['generate_content_format'] = $request_settings['generate_content_format'] ?? 'generateContent';
        $request_settings['api_key'] = K_API_GEMINI;

        // Construir la URL de la API
        $url = $this->build_url($request_settings);

        // Preparando el contenido para la API
        $requestData = [
            "contents" => $request_settings['contents'],
            "system_instruction" => [
                'parts' => $request_settings['system_instruction_parts']
            ],
            "generationConfig" => [
                "temperature" => 1.6,
                "maxOutputTokens" => 1000,
                "responseMimeType" => "text/plain"
            ],
        ];

        $payload = json_encode($requestData);

        $responseData = $this->execute_request($url, $payload);
        //$responseData = $this->generate_mock($url, $payload);

        $responseData['response_text'] = 'Ocurrió un error al obtener la respuesta.';
        if (isset($responseData['response']['candidates'][0]['content']['parts'][0]['text'])) {
            $response_text = $responseData['response']['candidates'][0]['content']['parts'][0]['text'];
            $responseData['response_text'] = $response_text;
        }

        // Preparar la respuesta
        $data = [
            'model_id' => $request_settings['model_id'],
            'response_text' => $responseData['response_text'] ?? '',
            'response_details' => $responseData['response'] ?? [],
            'error' => ''
        ];
        
        return $data;
    }

    /**
     * Ejecuta una solicitud HTTP POST a la API de Gemini.
     * 2025-05-25
     * @param string $url La URL de la API a la que se enviará la solicitud.
     * @param string $payload El cuerpo de la solicitud en formato JSON.
     */
    function execute_request($url, $payload)
    {
        // Valores por defecto
        $data['error'] = '';
        $data['response'] = [];

        // Ejecutar la solicitud
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            $data['error'] = $curl_error;
        } else {
            $data['response'] = json_decode($response, true);
        }

        if ($http_code !== 200) {
            $data['error'] = 'API request failed with status ' . $http_code . ': ' . $response;
        } else {
            $data['response'] = json_decode($response, true);
        }

        return $data;
    }

    /**
     * Construye la URL para la solicitud a la API de Gemini.
     * 2025-08-21
     */
    function build_url($request_settings)
    {
        // Construir la URL de la API
        $url = "https://generativelanguage.googleapis.com/v1beta/models/";
        $url .= "{$request_settings['model_id']}:";
        $url .= "{$request_settings['generate_content_format']}";
        $url .= "?key={$request_settings['api_key']}";

        return $url;
    }

    /**
     * Simular una respuesta de API de Gemini predefinida y estática para pruebas de 
     * interacción en desarrollo de frontend
     * 2025-08-05
     */
    function generate_mock()
    {
        $data = [
            'response' => [
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Esta es una respuesta simulada para pruebas: ' . date('Y-m-d H:i:s')]
                            ]
                        ]
                    ]
                ],
                'modelVersion' => 'gemini-2.0-flash-lite',
                'usageMetadata' => [
                    'promptTokenCount' => 10,
                    'candidatesTokenCount' => 20
                ]
            ]
        ];

        return $data;
    }

    /**
     * Texto con la instrucción de generación o procesamiento que debe ejecutar la IA.
     * 
     * 2025-08-04
     * @param string $key :: Clave para identificar la instrucción del sistema.
     * @return string :: Texto con la instrucción de generació o procesamiento que debe ejecutar la IA.
     */
    function system_instruction($key = 'ayudante', $folder = 'ai_system_instructions')
    {
        $file_content = file_get_contents(PATH_CONTENT . $folder . '/' . $key . '.md');
        $file_content = str_replace("\r\n", "\n", $file_content); // Normalizar saltos de línea
        $file_content = str_replace("\n", ' ', $file_content); // Reemplazar saltos de línea por espacios
        $file_content = trim($file_content); // Eliminar espacios al inicio y al final

        return $file_content;
    }
}