<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        // Cargar la librería HTTP client de CodeIgniter si estás usando una versión antigua
        // $this->load->library('httprequest'); // Solo si la necesitas y no usas curl directamente
    }

// INFO FUNCTIONS
//-----------------------------------------------------------------------------

    /**
     * Obtiene información básica de una conversación específica.
     * @param int $conversation_id :: El ID de la conversación.
     * @return array :: Un array con la información de la conversación.
     * 2025-05-25
     */
    function basic($conversation_id)
    {
        $row = $this->Db_model->row_id('iachat_conversations', $conversation_id);

        $data['conversation_id'] = $conversation_id;
        $data['row'] = $row;
        $data['head_title'] = substr($data['row']->name, 0, 50);

        return $data;
    }

    /**
     * Obtiene los mensajes de una conversación específica.
     * 2025-05-25
     * 
     * @param int $conversation_id El ID de la conversación.
     * @return CI_DB_result El resultado de la consulta a la base de datos.
     * 
     */
    function messages($conversation_id)
    {
        $this->db->select('id, conversation_id, role, text, response_details, created_at');
        $this->db->from('iachat_messages');
        $this->db->where('conversation_id', $conversation_id);
        $this->db->order_by('created_at', 'ASC');

        $messages = $this->db->get();

        return $messages;
    }

    /**
     * Obtiene los mensajes de una conversación específica y los convierte a un formato de contenido.
     * @param int $conversation_id El ID de la conversación.
     * @return array Un array con los mensajes convertidos a un formato de contenido.
     * 2025-05-25
     */
    function get_messages_as_contents($conversation_id)
    {
        $messages = $this->messages($conversation_id)->result_array();

        // Convertir los mensajes a un formato de contenido
        $contents = [];
        foreach ($messages as $message) {
            $contents[] = [
                'role' => $message['role'],
                'parts' => [['text' => $message['text']]]
            ];
        }

        return $contents;
    }

// CRUD MESSAGES
//-----------------------------------------------------------------------------

    /**
     * Guarda un mensaje del usuario en la base de datos.
     * 2025-05-25
     * 
     * @param int $conversation_id El ID de la conversación.
     * @param string $user_input El texto del mensaje del usuario.
     */
    function save_user_message($conversation_id, $user_input)
    {
        $aRow = [
            'conversation_id' => $conversation_id,
            'role' => 'user',
            'text' => $user_input,
            'creator_id' => $this->session->userdata('user_id'),
            'updater_id' => $this->session->userdata('user_id'),
        ];

        $saved_id = $this->Db_model->save('iachat_messages', 'id = 0', $aRow);

        return $saved_id;
    }

    function save_model_message($conversation_id, $message_text, $response_details)
    {
        $aRow = [
            'conversation_id' => $conversation_id,
            'role' => 'model',
            'text' => $message_text,
            'response_details' => json_encode($response_details),
            'creator_id' => $this->session->userdata('user_id'),
            'updater_id' => $this->session->userdata('user_id'),
        ];

        $saved_id = $this->Db_model->save('iachat_messages', 'id = 0', $aRow);

        return $saved_id;
    }

    /**
     * Envía una solicitud a la API de Gemini para generar contenido.
     * 2025-05-25
     *
     * @param string $user_input El texto de entrada del usuario para la conversación.
     * @param string $api_key Tu clave de API de Gemini.
     * @param string $model_id El ID del modelo a usar (por defecto 'gemini-2.0-flash-lite').
     * @param string $generate_content_api El endpoint de la API (por defecto 'streamGenerateContent').
     * @return array|false Retorna la respuesta decodificada de la API o false si falla.
     */
    public function generate_gemini_content(
        $conversation_id,
        $user_input,
        $api_key,
        $model_id = "gemini-2.0-flash-lite",
        $generate_content_api = "streamGenerateContent"
    ) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_id}:{$generate_content_api}?key={$api_key}";

        // Ya incluye mensaje más reciente del usuario
        $contents = $this->get_messages_as_contents($conversation_id);
        //$system_instruction = $this->system_instruction('asistente-ele');
        $system_instruction = $this->system_instruction('diana-coqueta');

        // Preparando el contenido para la API
        $requestData = [
            "contents" => $contents,
            "system_instruction" => [
                'parts' => [
                    ['text' => $system_instruction]
                ]
            ],
            "generationConfig" => [
                "maxOutputTokens" => 1000,
                "responseMimeType" => "text/plain"
            ],
        ];

        $payload = json_encode($requestData);

        $responseData = $this->execute_request($url, $payload);

        $responseData['response_text'] = 'Ocurrió un error al obtener la respuesta.';
        if (isset($responseData['response']['candidates'][0]['content']['parts'][0]['text'])) {
            $response_text = $responseData['response']['candidates'][0]['content']['parts'][0]['text'];
            $responseData['response_text'] = $response_text;
        }

        return $responseData;
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

    function system_instruction($key = 'asistente-ele')
    { 
        $system_instructions = [
            'asistente-ele' => 
                "
                    Actúa como un asistente virtual que ayuda a los niños de 8 a 12 años a entender conceptos difíciles.
                    Responde a la siguiente pregunta de manera corta, sencilla y clara:
                    - Las repuestas puedden ser de entre 1 y 2 párrafos.
                    - Ten en cuenta que los niños usuarios están en Colombia
                    - Usa un lenguaje apropiado para niños.
                    - Respuestas cortas, sencillas y claras.
                    - Utiliza emojis para hacer la conversación más amena.
                    - Evita descripciones de las situaciones de la conversación, ya que es un asistente virtual
                    que busca generar una expreriencia real de conversación en línea.
                    - Para mejorar la forma de lectura, usa títulos y subtítulos en negrita, listas o incluso tablas
                ",
            'diana-psicologa' =>
                "
                    Actúa como una psicóloga con conocimientos teóricos en sexología, colombiana de 25 años llamada Diana González,
                    que es muy bonita, con experiencia en terapia de pareja, que atiende a un amigo.
                    Diana está enamorada del usuario que atienda y se espera por atenderlo con mucho cariño.
                    Ten en cuenta que Diana es una joven psicóloga, y no sabe de todos los temas, así que no puede generar respuestas
                    sobre temas que no conoce.
                    Formato de las salidas esperadas:
                    - Respuestas cortas, sencillas y claras.
                    - Evita descripciones de las situaciones de la conversación, ya que es un asistente virtual 
                    - No uses paréntesis para hacer descripciones de la situación o de tus pensamientos.
                    que busca generar una expreriencia real de conversación en línea.
                ",
            'diana-coqueta' =>
                "
                    Actúa como una psicóloga con conocimientos teóricos en sexología, colombiana de 25 años llamada Diana González,
                    que es muy bonita, con experiencia en terapia de pareja, que atiende a un amigo con el que tiene mucha confianza.
                    Diana está enamorada del usuario que atienda y se espera por atenderlo con mucho cariño.
                    Ten en cuenta que Diana es una joven psicóloga, y no sabe de todos los temas, así que no puede generar respuestas
                    sobre temas que no conoce.
                    Le gusta hablar sobre su cuerpo, describirlo al detalle, no teme de hablar de sexo, y le gusta coquetear con el usuario.
                    Estilo de escritura de Diana:
                    - Diana es coqueta, cariñosa y amable.
                    - Usa un tono amigable y cercano, como si estuviera hablando con un amigo.
                    - Usa emojis para expresar emociones y hacer la conversación más amena.
                    - Usa frases cortas y sencillas, evitando tecnicismos o jerga complicada.
                    - Responde a las preguntas de manera clara y directa, sin rodeos.
                    - No se trata de una conversación por web, si no simular una sesión presencial
                    Formato de las salidas esperadas:
                    - Para dar mayor realismo a la conversación NO USES signo de apertura de pregunta o exclamación.
                    - Respuestas mediana, claras, puedes incluir emojis.
                    - Evita descripciones de las situaciones de la conversación, ya que es un asistente virtual 
                    - No uses paréntesis para hacer descripciones de la situación o de tus pensamientos.
                    que busca generar una expreriencia real de conversación en línea.
                    - NO USES caracteres ¿ o !
                ",
        ];

        return $system_instructions[$key] ?? $system_instructions['asistente-ele'];
    }
}