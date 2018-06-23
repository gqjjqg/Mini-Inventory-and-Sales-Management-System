<?php
defined('BASEPATH') OR exit('');

/**
 * Description of Customer
 *
 * @author Amir <amirsanni@gmail.com>
 * @date 4th RabThaani, 1437AH (15th Jan, 2016)
 */
class Market extends CI_Model{
    public function __construct(){
        parent::__construct();
    }

    /**
     * [getAll description]
     * @param  [type]  $orderBy     [description]
     * @param  [type]  $orderFormat [description]
     * @param  integer $start       [description]
     * @param  string  $limit       [description]
     * @return [type]               [description]
     */
    public function getAll($orderBy, $orderFormat, $start=0, $limit='', $filter='', $status='') {
        $q = "SELECT market.id, product.Name ProductName, company.Name CustomerName, rel_project_product_competitor.CompetitorName,
                product_status.Name StatusName, product_platform.Name PlatformName, market.Active,
                IFNULL(customer_project.Name,'') ProjectName, market.StatusDate, market.AddedDate, market.UpdatedDate, market.Notes
            FROM market
            LEFT JOIN (
                 SELECT MarketID, group_concat(company.Name) as CompetitorName from rel_project_product_competitor
                 JOIN company ON company.ID = rel_project_product_competitor.CompetitorID group by MarketID
             ) rel_project_product_competitor ON rel_project_product_competitor.MarketID = market.id

            JOIN company ON market.CustomerID = company.id
            JOIN product ON market.ProductID = product.id
            JOIN product_platform ON market.PlatformID = product_platform.id
            JOIN product_status ON market.StatusID = product_status.id
            LEFT JOIN customer_project ON market.ProjectID = customer_project.id
            ";

        if ($filter != '') {
            $q = $q." WHERE market.ProductID = {$filter}";
        }
        if ($status != '') {
            if ($filter != '' ) {
                $q = $q." AND market.Active = {$status}";
            } else {
                $q = $q." WHERE market.Active = {$status}";
            }
        }

        $q = $q." ORDER BY {$orderBy} {$orderFormat} LIMIT {$limit} OFFSET {$start}";

        $run_q = $this->db->query($q);

        if($run_q->num_rows() > 0) {
            return $run_q->result();
        } else {
            return FALSE;
        }
    }

    /**
     *
     * @param type $itemName
     * @param type $itemQuantity
     * @param type $itemPrice
     * @param type $itemDescription
     * @param type $itemCode
     * @return boolean
     */
    public function add($itemProduct, $customer, $platform, $status, $itemProject, $itemDate, $itemDesc) {
        $data = ['ProductID'=>$itemProduct, 'CustomerID'=>$customer, 'PlatformID'=>$platform,
        'StatusID'=>$status, 'ProjectID'=>$itemProject, 'Notes'=>$itemDesc];

        if ($itemDate !== "") {
            $this->db->set('StatusDate', $itemDate);
        } else {
            $this->db->set('StatusDate', NULL);
        }
        //set the datetime based on the db driver in use
        $this->db->platform() == "sqlite3"
                ?
        $this->db->set('AddedDate', "datetime('now')", FALSE)
                :
        $this->db->set('AddedDate', "NOW()", FALSE);

        $this->db->insert('market', $data);

        if($this->db->insert_id()){
            return $this->db->insert_id();
        } else {
            return FALSE;
        }
    }

    /**
     * count all records
     * @param  string $filter [description]
     * @return [type]         [description]
     */
    public function countAll($filter='', $status='') {
        if ($filter != '') {
            $this->db->where("ProductID", $filter);
        }
        if ($status != '') {
            $this->db->where("Active", $status);
        }
        $run_q = $this->db->get("market");
        return $run_q->num_rows();
    }


    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */

    /**
     *
     * @param type $value
     * @return boolean
     */
    public function itemsearch($value){
        $q = "SELECT xscp.id, product.Name ProductName, customer.Name CustomerName, IFNULL(customer_vender.Name,'') CompetitorName,
                    priority.Name PriorityName, product_status.Name StatusName, priority.Value PriorityValue, product_platform.Name PlatformName,
                    IFNULL(customer_project.Name,'') ProjectName, xscp.MilestoneDate, xscp.AddedDate, xscp.UpdatedDate, xscp.Notes
                FROM xscp
                join product ON xscp.ProductID = product.id
                join customer ON xscp.CustomerID = customer.id
                left join customer_vender ON xscp.VenderID = customer_vender.id
                left join customer_project ON xscp.ProjectID = customer_project.id
                join product_status ON xscp.StatusID = product_status.id
                join priority ON xscp.PriorityID = priority.id
                join product_platform ON xscp.PlatformID = product_platform.id
                WHERE
                product.Name LIKE '%".$this->db->escape_like_str($value)."%'";

        $run_q = $this->db->query($q, [$value, $value]);

        if($run_q->num_rows() > 0){
            return $run_q->result();
        }

        else{
            return FALSE;
        }
    }

   /**
    *
    * @param type $itemId
    * @param type $itemName
    * @param type $itemDesc
    * @param type $itemPrice
    */
   public function edit($itemId, $itemProductID, $itemCustomerID, $itemPlatformID, $itemStatusID, $itemProjectName, $itemStatusDate, $itemDesc){
       $data = ['ProductID'=>$itemProductID, 'CustomerID'=>$itemCustomerID, 'PlatformID'=>$itemPlatformID,
       'StatusID'=>$itemStatusID, 'ProjectID'=>$itemProjectName, 'Notes'=>$itemDesc];
       if ($itemStatusDate !== "") {
           $this->db->set('StatusDate', $itemStatusDate);
       } else {
           $this->db->set('StatusDate', NULL);
       }
       $this->db->where('id', $itemId);
       $this->db->update('market', $data);

       return TRUE;
   }

}