<?php
	
	/*
 * Import Driver for type: DateTime
 */

require_once(__DIR__ . '/ImportDriver_default.php');

class ImportDriver_datetime extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_select()
    {
        $this->type = 'datetime';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        $data = $this->field->prepareImportValue($value, ImportableField::STRING_VALUE, $entry_id);
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export(array $data, $entry_id = null)
    {
	    
	   /* 
	    if (is_array($data['value'])) {
            $newData = array();
            foreach ($data['value'] as $value) {
                $newData[] = trim($value);
            }

            return implode(', ', $newData);
        } else {
            return trim($data['value']);
        }
        
        */
	    
	  /*  var_dump($data);die; */
	  
	  
        $data = $this->field->prepareExportValue($data, ExportableField::LIST_OF + ExportableField::VALUE, $entry_id);
        return implode(',', $data);
      //  return implode($data);
       
    }

}