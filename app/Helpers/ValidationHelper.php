<?php namespace App\Helpers;

use App\Models\AttributeMapping;
use App\Models\ValueMapping;

/**
 * ValidationHelper.php
 *
 * Created: 19/08/2019
 *
 * @author Mehdi Mehtarizadeh
 *
 * This class contains helper functions for form validation.
 */

class ValidationHelper{

    private $db;


    function __construct()
    {

    }

    /**
     * unique_network_group_name_check
     * @param string $group_name the user input to be checked for uniqueness.
     * @param string $network_key the network key accompanying group name.
     *
     * @return bool true if the network group name does not exist in the database, false otherwise.
     *
     * @author Mehdi Mehtarizadeh
     */

    function unique_network_group_name_check(string $group_name, string $network_key): bool
    {
        $networkGroupModel = new \App\Models\NetworkGroup();
        return ($networkGroupModel->getNetworkGroups('', array('network_key' => $network_key, 'name' => $group_name)) ? false : true);
    }

    public function valid_delimiter(string $delimiter): bool
    {
        $valid_delimiters = [',', '/', ';', ':', '|', '*', '&', '%', '$', '!', '~', '#', '-', '_', '+', '=', '^'];

        return in_array($delimiter, $valid_delimiters);
    }

    public function subject_id_required_with(string $str, string $fields, array $data, & $err): bool
    {
        $err = null;

        if ($data[$fields] == SUBJECT_ID_IN_FILE_NAME) {
            return true;
        }

        if ($data[$fields] == SUBJECT_ID_WITHIN_FILE ) {

            if ($str == '' && $str == null) {
                $err = "Subject ID Attribute Name cannot be empty.";
                return false;

            }
            else{
                $regexp = "/^[a-zA-Z0-9-_]+$/";

                if (preg_match($regexp, $str, $matches)) {
                    return true;
                }

                $err = "The only valid input for {field} is alphanumeric characters, dashes, and underscores.";
            }
        }

        return false;
    }

    public function group_columns_required_with(string $str, string $fields, array $data, & $err): bool
    {
        $err = null;

        if ($data[$fields] == GROUPING_COLUMNS_ALL) {
            return true;
        }

        if ($data[$fields] == GROUPING_COLUMNS_CUSTOM) {

            if ($str == null || $str == '') {
                $err = 'Group Columns cannot be empty when custom grouping is selected.';
                return false;
            }
            else {
                if(strpos($str, ',')){
                    $items = explode(',', $str);

                    foreach ($items as $item) {
                        if (intval($item) == 0) {
                            $err = 'Group Columns should be a comma separated list of numbers.';
                            return false;
                        }
                    }

                    return true;
                }
                elseif (is_numeric($str)){
                	return true;
				}
            }
        }


        return false;
    }

	public function unique_ontology_prefix(string $str, string $fields, array $data, & $err): bool
	{
		$prefixModel = new \App\Models\OntologyPrefix();
		if (strpos($fields, ',') !== false){
			$fieldsArr = explode(',', $fields);
			$ontology_id = $data['ontology_id'];
			$relationship_id = $data['prefix_id'];
			return !$prefixModel->ontologyPrefixExists($str, $ontology_id, $relationship_id);

		}
		else{
			$ontology_id = $data[$fields];
			return !$prefixModel->ontologyPrefixExists($str, $ontology_id);
		}
	}

	public function unique_ontology_relationship(string $str, string $fields, array $data, & $err): bool
	{
		$relationshipModel = new \App\Models\OntologyRelationship();
		if (strpos($fields, ',') !== false){
			$fieldsArr = explode(',', $fields);
			$ontology_id = $data['ontology_id'];
			$relationship_id = $data['relationship_id'];
			return !$relationshipModel->ontologyRelationshipExists($str, $ontology_id, $relationship_id);

		}
		else{
			$ontology_id = $data[$fields];
			return !$relationshipModel->ontologyRelationshipExists($str, $ontology_id);
		}
	}

	public function unique_attribute_mapping(string $str, string $fields, array $data, & $err): bool
	{
		$attributeMappingModel = new AttributeMapping();
		$source_id = $data[$fields];
		return !$attributeMappingModel->attributeMappingExists($str, $source_id);
	}

	public function unique_value_mapping(string $str, string $fields, array $data, & $err): bool
	{
		$valueMappingModel = new ValueMapping();
		$attribute_id = $data[$fields];
		return !$valueMappingModel->valueMappingExists($str, $attribute_id);
	}
}
