<?php
class Sync_model extends CI_Model{
    
    /**
     * Exportar tabla a JSON en partes de N registros.
     *
     * @param string $table_name Nombre de la tabla
     * @param int    $chunk_size  Número de registros por archivo (default: 10000)
     * @return array Resultado con mensaje y lista de archivos
     */
    function export_table_to_json($table_name, $chunk_size = 10000)
    {
        //Limpiar carpeta antes de generar
        $this->delete_generated_files($table_name);

        //Prefijo de 24 caracteres alfanuméricos aleatorios para el nombre de archivos 
        $prefix = substr(md5(uniqid(rand(), true)), 0, 24);

        // 📂 Carpeta destino en content/database/{table_name}/
        $folder_path = FCPATH . "content/database/" . $table_name . "/";
        
        if (!is_dir($folder_path)) {
            mkdir($folder_path, 0777, true);
        }

        // Total de registros y número de partes
        $qty_rows = $this->db->count_all($table_name);
        $parts = (int) ceil($qty_rows / $chunk_size);

        $files = array();

        for ($part = 1; $part <= $parts; $part++) {
            $offset = ($part - 1) * $chunk_size;

            $this->db->select('*');
            $this->db->limit($chunk_size, $offset);
            $query = $this->db->get($table_name);
            $rows = $query->result_array();

            $file_name = "{$prefix}_{$part}.json";
            $file_path = $folder_path . $file_name;

            // Guardar con formato legible
            $json_data = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($file_path, $json_data);

            $files[] = $file_name;
        }

        return array(
            'message' => "Exportación completa: {$parts} archivo(s) JSON generado(s)",
            'files'   => $files,
            'qty_rows' => $qty_rows,
            'qty_files'   => count($files),
            'prefix'   => $prefix,
            'status' => 1
        );
    }

    /**
     * Sincroniza los archivos JSON de una carpeta con la tabla correspondiente.
     * Si el registro existe (por id), lo actualiza; si no, lo inserta.
     * 2025-10-04
     *
     * @param string $table_name  Nombre de la tabla
     * @param string $primary_key Nombre de la llave primaria (por defecto 'id')
     * @return array
     */
    function sync_table($table_name, $primary_key = 'id')
    {
        $this->load->helper('file');

        $folder_path = FCPATH . "content/database/" . $table_name . "/";

        if (!is_dir($folder_path)) {
            return array(
                'status' => 0,
                'message' => "No existe la carpeta: {$folder_path}",
                'inserted' => 0,
                'updated'  => 0,
            );
        }

        $files = get_filenames($folder_path);
        
        $inserted = 0;
        $updated  = 0;
        
        //Validar que files sea array
        if ( ! is_array($files) ) { $files = array(); }

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') continue;

            $file_path = $folder_path . $file;
            $json = file_get_contents($file_path);
            $records = json_decode($json, true);

            if (!is_array($records)) continue;

            foreach ($records as $record) {
                if (!isset($record[$primary_key])) continue;

                // Verificar si existe
                $this->db->where($primary_key, $record[$primary_key]);
                $query = $this->db->get($table_name);
                $exists = $query->row_array();

                if ($exists) {
                    $this->db->where($primary_key, $record[$primary_key]);
                    $this->db->update($table_name, $record);
                    $updated++;
                } else {
                    $this->db->insert($table_name, $record);
                    $inserted++;
                }
            }
        }

        // Actualizar el campo sync_key en la tabla sis_tabla
        $arr_row['fecha_sincro'] = date('Y-m-d H:i:s');
        $arr_row['cant_registros'] = $inserted + $updated;
        
        $this->db->where('nombre_tabla', $table_name);
        $this->db->update('sis_tabla', $arr_row); 

        return array(
            'status' => 1,
            'message' => "Sincronización completada para {$table_name}",
            'inserted' => $inserted,
            'updated'  => $updated,
        );
    }

    /**
     * Elimina los archivos JSON generados para una tabla específica.
     * 2025-10-04
     *
     * @param string $table_name Nombre de la tabla
     * @return array Resultado con mensaje y cantidad de archivos eliminados
     */
    function delete_generated_files($table_name)
    {
        $this->load->helper('file');

        // Normaliza y construye la ruta completa
        $folder_path = rtrim(FCPATH . "content/database/" . $table_name . "/", '/\\') . '/';

        // Validar existencia de carpeta
        if (!is_dir($folder_path)) {
            return array(
                'status' => 0,
                'message' => "No existe la carpeta: {$folder_path}",
                'deleted_files' => 0
            );
        }

        $files = get_filenames($folder_path);
        $deleted_count = 0;
        $errors = array();
        
        //Validar que files sea array
        if ( ! is_array($files) ) { $files = array(); }

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'json') {
                continue; // ignorar otros archivos
            }

            $file_path = $folder_path . $file;

            // Validar existencia antes de intentar eliminar
            if (is_file($file_path)) {
                if (@unlink($file_path)) {
                    $deleted_count++;
                } else {
                    $errors[] = basename($file);
                }
            }
        }

        // Mensaje resumen
        $message = "Eliminación completa: {$deleted_count} archivo(s) eliminado(s)";
        if (!empty($errors)) {
            $message .= " No se pudieron eliminar: " . implode(', ', $errors);
        }

        return array(
            'status' => empty($errors) ? 1 : 0,
            'message' => $message,
            'deleted_files' => $deleted_count,
            'failed_files' => $errors
        );
    }

    /**
     * Obtiene todas las tablas de la base de datos
     * 2025-10-04
     *
     * @return array
     */
    function tables()
    {
        $this->db->select('*');
        $this->db->from('sis_tabla');
        $this->db->order_by('nombre_tabla', 'ASC');
        $query = $this->db->get();
        return $query;
    }   
}