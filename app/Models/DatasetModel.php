<?php
namespace App\Models;
use CodeIgniter\Model;
class DatasetModel extends Model
{
    protected $DBGroup = 'lehmr';
    protected $table = 'dataset';
    protected $primaryKey = 'd_id';
    protected $allowedFields = [
        'u_id', 'd_uniqueid', 'd_title', 'd_abstract', 'd_theme', 'd_researchstudy',
        'd_datatypes', 'd_ethnicities', 'd_funders', 'd_geographies', 'd_keywords',
        'd_agerange', 'd_studysize', 'd_controler', 'd_arights', 'd_legaljurisdiction',
        'd_organisation', 'd_conpoint', 'd_approved', 'd_rejected', 'd_hdrconsent',
        'd_revisions', 'created_at', 'modified_at', 'archived'
    ];



    public function getUniqueTitles(): array
    {
        try {
            // Fetch all records with only d_title to avoid unnecessary data retrieval
            $results = $this->select('d_title')->findAll();

            $uniqueTitles = [];

            foreach ($results as $row) {
                // Add only unique titles
                if (!in_array($row['d_title'], $uniqueTitles)) {
                    $uniqueTitles[] = $row['d_title'];
                }
            }

            return $uniqueTitles;

        } catch (\Exception $e) {
            // Handle any potential database errors
            log_message('error', 'Error fetching unique titles: ' . $e->getMessage());
            return []; // Return an empty array if there's an error
        }
    }

    public function getUniqueKeywords(): array
    {
        try {
            // Fetch all records with only d_keywords
            $results = $this->select('d_keywords')->findAll();

            $uniqueKeywords = [];

            foreach ($results as $row) {
                // Split d_keywords by ";" to handle multiple keywords
                $keywords = explode(';', $row['d_keywords']);

                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword); // Trim whitespace

                    // Add only unique keywords
                    if (!in_array($keyword, $uniqueKeywords)) {
                        $uniqueKeywords[] = $keyword;
                    }
                }
            }

            return $uniqueKeywords;

        } catch (\Exception $e) {
            // Handle any potential database errors
            log_message('error', 'Error fetching unique keywords: ' . $e->getMessage());
            return []; // Return an empty array if there's an error
        }
}



    // Method to count total records (without filters)
    public function countAllDatasets()
    {
        return $this->db->table($this->table)->countAllResults();
    }


    // public function getFilteredData($start, $length, $searchValue, $orderColumn, $orderDir, $d_datatitle, $d_datatype, $d_datatheme, $d_studysize)
    // {
    //     $query = $this->db->table($this->table);

    //     if (!empty($searchValue)) {
    //         $query->like('d_title', $searchValue)
    //             ->orLike('d_abstract', $searchValue)
    //             ->orLike('d_theme', $searchValue);
    //     }

    //     if (!empty($d_datatitle)) {
    //         $query->groupStart();  // Start a grouped condition for d_datatitle
    //         foreach ($d_datatitle as $titleOrKeyword) {
    //             $query->orWhere('d_title', $titleOrKeyword)
    //                 ->orWhere('d_keywords', $titleOrKeyword);
    //         }
    //         $query->groupEnd();  // End the grouped condition
    //     }

    //     if (!empty($d_datatype)) {
    //         $query->whereIn('d_datatypes', $d_datatype);
    //     }
    //     if (!empty($d_datatheme)) {
    //         $query->where('d_theme', $d_datatheme);
    //     }
    //     if (!empty($d_studysize)) {
    //         $query->where('d_studysize >=', $d_studysize);
    //     }

    //     $columns = ['d_id', 'd_title', 'd_abstract', 'd_theme', 'created_at'];
    //     $query->orderBy($columns[$orderColumn], $orderDir);

    //     return $query->get($length, $start)->getResultArray();
    // }

    // public function countFilteredData($searchValue, $d_datatitle, $d_datatype, $d_datatheme, $d_studysize)
    // {
    //     $query = $this->db->table($this->table);

    //     if (!empty($searchValue)) {
    //         $query->like('d_title', $searchValue)
    //             ->orLike('d_abstract', $searchValue)
    //             ->orLike('d_theme', $searchValue)
    //             ->orLike('d_datatypes', $searchValue);
    //     }

    //     if (!empty($d_datatitle)) {
    //         $query->groupStart();
    //         foreach ($d_datatitle as $titleOrKeyword) {
    //             $query->orLike('d_title', $titleOrKeyword)
    //                 ->orLike('d_keywords', $titleOrKeyword);
    //         }
    //         $query->groupEnd();
    //     }

    //     if (!empty($d_datatype)) {
    //         $query->groupStart();
    //         foreach ($d_datatype as $d){
    //             $query->where('d_datatypes Like', $d);
    //         }
    //         $query->groupEnd();
    //     }
    //     if (!empty($d_datatheme)) {
    //         $query->where('d_theme', $d_datatheme);
    //     }
    //     if (!empty($d_studysize)) {
    //         $query->where('d_studysize >=', $d_studysize);
    //     }

    //     return $query->countAllResults();
    // }

    public function getDatasetWithDetails($id)
    {
        $dataset = $this->find($id);

        if ($dataset) {
            $dataset['researchers'] = $this->db->table('person')
                                               ->where('d_id', $id)
                                               ->get()->getResultArray();

            $dataset['publications'] = $this->db->table('publications')
                                                ->where('d_id', $id)
                                                ->get()->getResultArray();

            $dataset['conditions'] = $this->db->table('conditions')
                                              ->where('d_id', $id)
                                              ->get()->getRowArray();

            return $dataset;
        } else {
            return null;
        }
    }

    public function getFilteredData(string $queryLogic, int $start, int $length, string $orderColumn, string $orderDir)
    {
        return $this->db->table($this->table)
            ->where($queryLogic)
            ->orderBy($orderColumn, $orderDir)
            ->limit($length, $start)
            ->get()
            ->getResultArray();
    }

    // public function countAllDatasets()
    // {
    //     return $this->db->table($this->table)->countAll();
    // }

    public function countFilteredData(string $queryLogic)
    {
        return $this->db->table($this->table)
            ->where($queryLogic)
            ->countAllResults();
    }
}
