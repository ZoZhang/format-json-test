<?php

declare(strict_types=1);

namespace App\Service;

use \ParseCsv\Csv as ParseCsv;

class Csv
{
    /**
     * Configure parameters of class ParseCsv
     */
    const CSV_OUTPUT_LINE_FEED = PHP_EOL;
    const CSV_OUTPUT_DELIMITER = ';';
    const CSV_OUTPUT_ENCODING = 'UTF-8';

    /**
     * @var array
     */
    protected $csvData = [];

    /**
     * @var ParseCsv
     */
    protected $parseCsv;

    /**
     * Mapping Column name for csv
     * @var []
     */
    protected $mappingColumns = [
        'teams' => [
            'squadName' => 'Squad Name',
            'homeTown' => 'HomeTown',
            'formed' => 'Formed Year',
            'secretBase' => 'Base',
            'numberMembers' => 'Number of members',
            'averageAge' => 'Average Age',
            'averageStrenghTeam' => 'Average strengh of team',
            'active' => 'Is Active'
        ],

        'team_members' => [
            'squadName' => 'Squad Name',
            'homeTown' => 'HomeTown',
            'name' => 'Name',
            'secretIdentity' => 'Secret ID',
            'age' => 'Age',
            'numberPower' => 'Number of Power',
            'averageStrenghMember' => 'Average strengh of member'
        ]
    ];

    /**
     * Mapping power name by code
     * @var []
     */
    protected $mappingPowers = [
        'RR'  => 'Radiation resistance',
        'TT'  => 'Turning tiny',
        'TB'  => 'Radiation blast',
        'MTP'  => 'Million tonne punch',
        'DR'  => 'Damage resistance',
        'SR'  => 'Superhuman reflexes',
        'IM'  => 'Immortality',
        'HI'  => 'Heat Immunity',
        'IF'  => 'Inferno',
        'TEL'  => 'Teleportation',
        'IT'  => 'Interdimensional travel',
        'CC'  => 'Cheese Control',
        'DRF'  => 'Drink really fast',
        'HSW'  => 'Hyper slowing writer',
        'AL'  => 'Always late',
        'J2F'  => 'Jump 2 feets up',
        'NSJ'  => 'Never stop jumping',
        'CAL'  => 'Cry a lot',
        'STC'  => 'Sing to charm',
        'IG'  => 'Infernal groove',
        'BAD'  => 'Burn all dancfloors',
        'M'  => 'Mortality',
        'INV'  => 'Invisibility'
    ];

    /**
     * Initialise the class and config
     * @param ParseCsv $parseCsv
     */
    public function __construct(ParseCsv $parseCsv)
    {
        $this->parseCsv = $parseCsv;

        // initialise the configuration of ParseCsv
        $this->configure();
    }

    /**
     * Configure parameters of the class
     */
    public function configure()
    {
        $this->parseCsv->linefeed = self::CSV_OUTPUT_LINE_FEED;
        $this->parseCsv->delimiter = self::CSV_OUTPUT_DELIMITER;
        $this->parseCsv->output_encoding = self::CSV_OUTPUT_ENCODING;
    }

    /**
     * Convert data JSON to Array
     * @param string $content
     * @return array|mixed
     */
    public function convert(string $content)
    {
        if (!$content) {
            throw new \InvalidArgumentException('The content must not be empty.');
        }

        $content = json_decode($content, true);

        if (!$content || !is_array($content)) {
            throw new \RuntimeException('The type of content is not an array.');
        }
        return $content;
    }

    /**
     * Generate csv file by JSON data
     * @param string $file
     * @param array $content
     * @param int $mode
     * @return bool
     */
    public function export(string $file, array $content, int $mode)
    {
        $mapppingField = $mode == 1 ? 'teams' : 'team_members';
        $csvData = $this->generateCsvData($mapppingField, $content);

        if ($csvData && count($csvData)) {
            // generate csv file by class ParseCsv
            $generated = $this->parseCsv->save(
                $file,
                $csvData['ndata'],
                false,
                $csvData['columns']
            );

            // check new csv file exist and display message
            if ($generated && file_exists($file)) {
                return $file;
            }
        }

        return false;
    }

    /**
     * Generate csv data for mapping fields
     * @param string $mapping
     * @param array $content
     * @return array
     */
    public function generateCsvData(string $mapping, array $content)
    {
        // reset data
        $this->csvData = [];

        // generate csv data in mapping fields
        foreach($this->mappingColumns[$mapping] as $fieldKey => $fieldLabel) {
            // adding columns name
            if (!isset($this->csvData['columns'])) {
                $this->csvData['columns'][] = $fieldLabel;
            } else {
                array_push($this->csvData['columns'], $fieldLabel);
            }

            // From a new array with column key
            if (!isset($this->csvData['cdata'])) {
                $this->csvData['cdata'] = [];
            }

            // Match the corresponding key data and form new data
            $columnData = array_column($content['teams'], $fieldKey);
            if ($columnData) {
                $this->csvData['cdata'][] = array_column($content['teams'], $fieldKey);
            } else {
                // combine new data by column name
                $this->csvData['cdata'][] = $this->combineColumnData($fieldKey, $content['teams']);
            }
        }

        // Handle power data specified
        if ('team_members' == $mapping) {
            $membersData = array_column($content['teams'], 'members');

            // Append the corresponding power code to the current item and keep the same index in the `cdata`
            $this->csvData['additional_data'] =  array_map(function($item){
                $addtionalCodeData = [];
                $powerItemData = array_column($item, 'powers');

                // Recursively all powers of the current item data, and add power code to the corresponding current item index and appends the column name
                array_walk_recursive($powerItemData, function ($code, $key) use (&$addtionalCodeData){
                    if ('power_code' == $key && isset($this->mappingPowers[$code])) {
                        // Save all power code of the current item
                        $addtionalCodeData[$code] = $code;

                        // Save all currently mapped column names
                        $this->csvData['additional_columns'][$code] = $this->mappingPowers[$code];
                    }
                });
                return $addtionalCodeData;
            }, $membersData);
        }

        // convert according to a row of csv data
        for($i = 0; $i < count(current($this->csvData['cdata'])); $i++) {
            $newData = array_column($this->csvData['cdata'], $i);
            $newData = $this->replaceBooleanWithLabel($newData);
            $this->csvData['ndata'][] = $newData;
        }

        // Handle power row data specified
        if ('team_members' == $mapping && isset($this->csvData['additional_columns']) ) {
            foreach($this->csvData['ndata'] as $idx => $nData) {
                foreach($this->csvData['additional_columns'] as $powerCode => $powerName) {
                    $addtionalData = $this->csvData['additional_data'][$idx];

                    // exception additional data
                    if (!$addtionalData) {
                        continue;
                    }

                    // If the additional data matches the power code, add the power code to the corresponding row data, otherwise leave it blank
                    if (isset($addtionalData[$powerCode])) {
                        $nData[] = $powerCode;
                    } else {
                        $nData[] = 'NaN';
                    }
                }

                $this->csvData['ndata'][$idx] = $nData;
            }

            // Reset the additional column index, and merge the additional column to current columns
            $this->csvData['columns'] = array_merge($this->csvData['columns'], array_values($this->csvData['additional_columns']));
        }

        return $this->csvData;
    }

    /**
     * Convert boolean value with label
     * @param array $data
     * @return array
     */
    public function replaceBooleanWithLabel(array $data)
    {
        return array_map(function($item){
            if (is_bool($item)) {
                return $item === true ? 'Oui' : 'Non';
            }
            return $item;
        }, $data);
    }

    /**
     * Combine all values of ','
     * @param array $data
     * @param string $field
     * @return string[]
     */
    public function implodeRowValues(array $data, string $field)
    {
        return array_map(function ($item) use ($field){
            $ndata = array_column($item['members'], $field);
            if ($ndata) {
                return implode(',', $ndata);
            }
            return 'Nan';
        }, $data);
    }

    /**
     * Get all values from specific key in a multidimensional array
     *
     * @referen https://www.php.net/manual/en/function.array-values.php#103905
     * @param $key string
     * @param $arr array
     * @return null|string|array
     */
    public function array_value_recursive($key, array $arr){
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val){
            if($k == $key) array_push($val, $v);
        });
        return count($val) > 1 ? $val : array_pop($val);
    }

    /**
     * @param array $data
     * @param string $field
     * @param string $mode
     * @return array|float[]|int[]|void
     */
    public function calculateRowValue(array $data, string $field, string $mode)
    {
        // Set powers special fields
        $nfield = in_array($field, ['numberPower', 'averageStrenghTeam', 'averageStrenghMember']) ? 'powers' : $field;

        // Match fields for processing
        switch($nfield) {
            case 'averageAge':
                return array_map(function ($item) {
                    $ndata = array_column($item['members'], 'age');
                    return array_sum($ndata) / count($ndata);
                }, $data);

            case 'numberMembers':
                return array_map(function ($item) {
                    return count($item['members']);
                }, $data);

            case 'powers':
                $memberTeamPowers = 0;
                if ('averageStrenghTeam' == $field) {
                    // Traverse all `strengh` data the teams
                    $teamStrengh = $this->array_value_recursive('strengh', $data);
                    if ($teamStrengh) {
                        $memberTeamPowers = count($teamStrengh);
                    }
                }

                // Calculate sum and average of `strengh`
                return array_map(function ($item) use($field, $mode, $memberTeamPowers) {
                    $total = 0;
                    $ndata = array_column($item['members'], 'powers');
                    // Traverse all `strengh` data the members
                    $strengh = $this->array_value_recursive('strengh', $ndata);
                    $memberPowers = count($strengh);

                    if ($strengh) {
                        $total = array_sum($strengh);
                    }

                    // Average in flat mode of team or members
                    if ('average' == $mode) {
                        if ('averageStrenghTeam' == $field) {
                            $total = $total / $memberTeamPowers;
                        } else {
                            $total = $total / $memberPowers;
                        }
                    }
                    return $total;
                }, $data);
        }
    }

    /**
     * Combine new data by column data
     * @param string $columnName
     * @param array $content
     * @return array|float[]|int[]|string[]|void[]
     */
    public function combineColumnData(string $columnName, array $content)
    {
        $combineData = [];
        switch($columnName) {
            // calculate the number of members
            case 'numberMembers':
                $combineData = $this->calculateRowValue($content, 'numberMembers', 'sum');
                break;

            // combine `age` and `name` value of members
            case 'age':
            case 'name':
                $combineData = $this->implodeRowValues($content, $columnName);
                break;

            // calculate the average age of members
            case 'averageAge':
                $combineData = $this->calculateRowValue($content, 'averageAge', 'average');
                break;

            // combine all `secretIdentity` value of members
            case 'secretIdentity':
                $combineData = $this->implodeRowValues($content, 'secretIdentity');
                break;

            // calculate the average strength of members
            case 'numberPower':
                $combineData = $this->calculateRowValue($content, 'numberPower', 'sum');
                break;

            // calculate the average strength of team
            case 'averageStrenghTeam':
                $combineData = $this->calculateRowValue($content, 'averageStrenghTeam', 'average');
                break;

            // calculate the average strength of members
            case 'averageStrenghMember':
                $combineData = $this->calculateRowValue($content, 'averageStrenghMember', 'average');
                break;
        }

        return $combineData;
    }
}