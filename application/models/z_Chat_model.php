<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
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

// GESTIÓN DE CONVERSACIONES
//-----------------------------------------------------------------------------

    /**
     * Guardar un registro en la tabla iachat_conversations
     * 2025-06-26
     * @param array $arr_row :: Array con el registro para guardar
     */
    function save_conversation($arr_row = null)
    {
        //Verificar si hay array con registro
        if ( is_null($arr_row) ) $arr_row = $this->aRow();

        //Verificar si tiene id definido, insertar o actualizar
        if ( ! isset($arr_row['id']) ) 
        {
            //No existe, insertar
            $this->db->insert('iachat_conversations', $arr_row);
            $conversationId = $this->db->insert_id();
        } else {
            //Ya existe, editar
            $conversationId = $arr_row['id'];
            unset($arr_row['id']);

            $this->db->where('id', $conversationId)->update('iachat_conversations', $arr_row);
        }

        $data['saved_id'] = $conversationId;
        return $data;
    }

    /**
     * Array from HTTP:POST, adding edition data
     * 2025-06-26
     * @param bool $data_from_post :: Especifica si se toma o no datos de $POST
     * @return array $arr_row :: Array con el registro para guardar
     */
    function aRow($data_from_post = TRUE)
    {
        $arr_row = array();

        if ( $data_from_post ) { $arr_row = $this->input->post(); }
        
        $arr_row['updater_id'] = $this->session->userdata('user_id');
        $arr_row['updated_at'] = date('Y-m-d H:i:s');
        $arr_row['creator_id'] = $this->session->userdata('user_id');
        $arr_row['created_at'] = date('Y-m-d H:i:s');
        
        if ( isset($arr_row['id']) )
        {
            unset($arr_row['creator_id']);
            unset($arr_row['created_at']);
        }

        return $arr_row;
    }

// GESTIÓN DE MENSAJES
//-----------------------------------------------------------------------------

    /**
     * Obtiene los mensajes de una conversación específica.
     * 2025-05-25
     * 
     * @param int $conversation_id El ID de la conversación.
     * @return CI_DB_result El resultado de la consulta a la base de datos.
     * 
     */
    function messages($conversation_id, $limit = 100)
    {
        $this->db->select('id, conversation_id, role, text, response_details, created_at');
        $this->db->from('iachat_messages');
        $this->db->where('conversation_id', $conversation_id);
        $this->db->order_by('created_at', 'ASC');
        $this->db->limit($limit);

        $messages = $this->db->get();

        return $messages;
    }

    /**
     * Obtiene los mensajes de una conversación específica y los convierte a
     * un formato de contenido para el payload del request a la API
     * 
     * @param int $conversation_id El ID de la conversación.
     * @return array Un array con los mensajes convertidos a un formato de contenido.
     * 2025-05-25
     */
    function get_messages_as_contents($conversation_id, $limit = 100)
    {
        $messages = $this->messages($conversation_id, $limit)->result_array();

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

    /**
     * Elimina todos los mensajes de una conversación
     * 2025-06-18
     * @param int $conversation_id :: ID de la conversación
     * @param int $user_id :: ID del usuario creador de la conversación
     */
    function clear_chat($conversation_id, $user_id)
    {
        $data['qty_deleted'] = 0;
        $conversation = $this->Db_model->row('iachat_conversations', "id = {$conversation_id} AND user_id = {$user_id}");
        if ( ! is_null($conversation) ) {
            $this->db->delete('iachat_messages', "conversation_id = {$conversation_id}");
            $data['qty_deleted'] = $this->db->affected_rows();
        }

        return $data;
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
        $arr_row = [
            'conversation_id' => $conversation_id,
            'role' => 'user',
            'text' => $user_input,
            'creator_id' => $this->session->userdata('user_id'),
            'updater_id' => $this->session->userdata('user_id'),
        ];

        $saved_id = $this->Db_model->save('iachat_messages', 'id = 0', $arr_row);

        return $saved_id;
    }

    /**
     * Guarda un mensaje del modelo en la base de datos.
     * 2025-05-25
     *
     * @param int $conversation_id El ID de la conversación.
     * @param string $message_text El texto del mensaje del modelo.
     * @param array $response_details Los detalles de la respuesta del modelo.
     * @return int El ID del mensaje guardado.
     */
    function save_model_message($conversation_id, $message_text, $response_details)
    {
        $arr_row = [
            'conversation_id' => $conversation_id,
            'role' => 'model',
            'text' => $message_text,
            'model_version' => $response_details['response']['modelVersion'] ?? '-',
            'prompt_token_count' => $response_details['response']['usageMetadata']['promptTokenCount'] ?? 0,
            'candidates_token_count' => $response_details['response']['usageMetadata']['candidatesTokenCount'] ?? 0,
            'response_details' => json_encode($response_details),
            'creator_id' => $this->session->userdata('user_id'),
            'updater_id' => $this->session->userdata('user_id'),
        ];

        $saved_id = $this->Db_model->save('iachat_messages', 'id = 0', $arr_row);

        return $saved_id;
    }

    /**
     * Envía una solicitud a la API de Gemini para generar contenido.
     * 2025-08-14
     *
     * @param array $request_settings :: Configuración de la solicitud.
     * @return array|false Retorna la respuesta decodificada de la API o false si falla.
     */
    public function generate_gemini_content($request_settings)
    {
        // Construir la URL de la API
        $url = "https://generativelanguage.googleapis.com/v1beta/models/";
        $url .= "{$request_settings['model_id']}:";
        $url .= "{$request_settings['generate_content_format']}";
        $url .= "?key={$request_settings['api_key']}";

        // Preparando el contenido para la API
        $requestData = [
            "contents" => $request_settings['contents'],
            "system_instruction" => [
                'parts' => [
                    ['text' => $request_settings['system_instruction']]
                ]
            ],
            "generationConfig" => [
                "temperature" => 1.6,
                "maxOutputTokens" => 1000,
                "responseMimeType" => "text/plain"
            ],
        ];

        $payload = json_encode($requestData);

        $responseData = $this->execute_request($url, $payload);
        //$responseData = $this->execute_request_testing($url, $payload);

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

    /**
     * Simular una respuesta de API de Gemini predefinida y estática para pruebas de 
     * interacción en desarrollo de frontend
     * 2025-08-05
     */
    function execute_request_testing()
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

    function conversation_summary($conversation_id)
    {
        $this->db->select('id, user_id, created_at');
        $this->db->from('iachat_conversations');
        $this->db->where('id', $conversation_id);
        $query = $this->db->get();

        return $query->row_array();
    }

    /**
     * Texto con la instrucción de generación o procesamiento que debe ejecutar la IA.
     * 
     * 2025-08-04
     * @param string $key :: Clave para identificar la instrucción del sistema.
     * @return string :: Texto con la instrucción de generació o procesamiento que debe ejecutar la IA.
     */
    function system_instruction($key = 'monitoria-ele')
    {
        $file_content = file_get_contents(PATH_CONTENT . 'iachats/system_instruction/' . $key . '.md');
        $file_content = str_replace("\r\n", "\n", $file_content); // Normalizar saltos de línea
        $file_content = str_replace("\n", ' ', $file_content); // Reemplazar saltos de línea por espacios
        $file_content = trim($file_content); // Eliminar espacios al inicio y al final

        return $file_content;
    }
}