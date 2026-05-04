<?php
/**
 * Clase FindBuy_CP_Validator
 * Maneja la verificación de códigos postales y la recomendación de tienda más cercana.
 */

if (!defined('ABSPATH')) {
    exit;
}

class FindBuy_CP_Validator
{

    private $csv_file;
    private $store_mapping;

    public function __construct()
    {
        $this->csv_file = FINDBUY_CORE_PATH . 'lista-codigos-postales-espana.csv';

        // Mapeo de Nombres de Provincias (del CSV) a nuestras 4 Tiendas
        // Basado en proximidad geográfica.
        $this->store_mapping = [
            // Región MADRID
            'MADRID' => 'Madrid',
            'TOLEDO' => 'Madrid',
            'GUADALAJARA' => 'Madrid',
            'CUENCA' => 'Madrid',
            'CIUDAD REAL' => 'Madrid',
            'AVILA' => 'Madrid',
            'SEGOVIA' => 'Madrid',
            'SALAMANCA' => 'Madrid',
            'VALLADOLID' => 'Madrid',
            'CACERES' => 'Madrid',
            'BADAJOZ' => 'Madrid',

            // Región ZARAGOZA
            'ZARAGOZA' => 'Zaragoza',
            'HUESCA' => 'Zaragoza',
            'TERUEL' => 'Zaragoza',
            'SORIA' => 'Zaragoza',
            'LLEIDA' => 'Zaragoza', // Cercano
            'TARRAGONA' => 'Zaragoza', // Cercano

            // Región LOGROÑO
            'RIOJA' => 'Logroño',
            'LA RIOJA' => 'Logroño',
            'RIOJA, LA' => 'Logroño', // Estándar INE
            'ALAVA' => 'Logroño',
            'BURGOS' => 'Logroño',
            'NAVARRA' => 'Logroño',
            'VIZCAYA' => 'Logroño',
            'GUIPUZCOA' => 'Logroño',
            'CANTABRIA' => 'Logroño',
            'PALENCIA' => 'Logroño',

            // Región VALENCIA
            'VALENCIA' => 'Valencia',
            'CASTELLON' => 'Valencia',
            'ALICANTE' => 'Valencia',
            'ALBACETE' => 'Valencia',
            'MURCIA' => 'Valencia',
            'ISLAS BALEARES' => 'Valencia',
            'BALEARES' => 'Valencia',

            // Otros
            'BARCELONA' => 'Zaragoza',
            'GIRONA' => 'Zaragoza',
            'CORUÑA, A' => 'Logroño',
            'A CORUÑA' => 'Logroño',
            'LUGO' => 'Logroño',
            'OURENSE' => 'Logroño',
            'PONTEVEDRA' => 'Logroño',
            'ASTURIAS' => 'Logroño',
            'LEON' => 'Logroño',
            'ZAMORA' => 'Madrid',
            'SEVILLA' => 'Madrid',
            'HUELVA' => 'Madrid',
            'CADIZ' => 'Madrid',
            'CORDOBA' => 'Madrid',
            'JAEN' => 'Madrid',
            'MALAGA' => 'Madrid',
            'GRANADA' => 'Madrid',
            'ALMERIA' => 'Valencia',
            'PALMAS, LAS' => 'Madrid',
            'LAS PALMAS' => 'Madrid',
            'SANTA CRUZ DE TENERIFE' => 'Madrid',
            'CEUTA' => 'Madrid',
            'MELILLA' => 'Madrid'
        ];
    }

    /**
     * Comprobar si el CP está en la lista específica de "Coincidencia Exacta" proporcionada por el cliente.
     * Estos CPs deben mostrar la Línea Sólida (Disponibilidad Exacta).
     */
    private function check_exact_cp_override($cp)
    {
        $cp = intval($cp);

        // LOGROÑO: 26001-26009
        if ($cp >= 26001 && $cp <= 26009)
            return 'Logroño';

        // MADRID: 28001-28055, 28071, 28523, 28903, 28917
        if (($cp >= 28001 && $cp <= 28055) || in_array($cp, [28071, 28523, 28903, 28917]))
            return 'Madrid';

        // ZARAGOZA: 50001-50019
        if ($cp >= 50001 && $cp <= 50019)
            return 'Zaragoza';

        // VALENCIA: 46001-46026, 46035, 46920
        if (($cp >= 46001 && $cp <= 46026) || in_array($cp, [46035, 46920]))
            return 'Valencia';

        return false;
    }

    /**
     * Buscar un CP en el CSV y determinar el estado.
     * 
     * @param string $cp
     * @return array
     */
    public function validate($cp)
    {
        if (!file_exists($this->csv_file)) {
            return ['status' => 'error', 'message' => 'Database not found.'];
        }

        $cp = trim($cp);
        $found_data = false;

        // Open CSV
        if (($handle = fopen($this->csv_file, "r")) !== FALSE) {
            // Cabecera: CP;POBLACION;MUNICIPIO;PROVINCIA;COMUNIDAD
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($data[0] === $cp) {
                    $found_data = [
                        'cp' => $data[0],
                        'poblacion' => $data[1],
                        'municipio' => $data[2],
                        'provincia' => $data[3],
                        'comunidad' => $data[4]
                    ];
                    break;
                }
            }
            fclose($handle);
        }

        // Logic Implementation

        // 0. Comprobar Anulaciones de CP Específicas (Petición del Cliente) - MOVIDO ARRIBA
        // Si está en la lista específica, forzar lógica de coincidencia EXACTA, incluso si no está en CSV.
        $override_store = $this->check_exact_cp_override($cp);

        if ($override_store) {
            // Si se encontraron datos, usarlos. Si no (CP falta en CSV), recurrir al Nombre de la Tienda.
            $municipio = $found_data ? $found_data['municipio'] : $override_store;
            $provincia = $found_data ? $found_data['provincia'] : $override_store;

            return [
                'status' => 'exact',
                'store' => $override_store,
                'municipio' => $municipio,
                'provincia' => $provincia
            ];
        }

        if (!$found_data) {
            return ['status' => 'not_found', 'message' => 'Código Postal no encontrado.'];
        }

        // 1. Comprobar Coincidencia EXACTA de Ciudad (Recurrir a Nombre de Municipio)
        $municipio = strtoupper(trim($found_data['municipio']));
        $provincia = strtoupper(trim($found_data['provincia']));

        $exact_cities = ['MADRID', 'VALENCIA', 'ZARAGOZA', 'LOGROÑO'];

        if (in_array($municipio, $exact_cities)) {
            $store_name = ucfirst(strtolower($municipio));
            if ($store_name == 'Logroño')
                $store_name = 'Logroño';

            return [
                'status' => 'exact',
                'store' => $store_name,
                'municipio' => $found_data['municipio'],
                'provincia' => $found_data['provincia']
            ];
        }

        // 2. Recomendación de Tienda Más Cercana
        $recommended_store = isset($this->store_mapping[$provincia]) ? $this->store_mapping[$provincia] : 'Madrid';

        return [
            'status' => 'nearby',
            'store' => $recommended_store,
            'municipio' => $found_data['municipio'],
            'provincia' => $found_data['provincia']
        ];
    }
}
