<?php
class Travel
{
    public $id;
    public $createdAt;
    public $employeeName;
    public $departure;
    public $destination;
    public $price;
    public $companyId;
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->createdAt = $data['createdAt'];
        $this->employeeName = $data['employeeName'];
        $this->departure = $data['departure'];
        $this->destination = $data['destination'];
        $this->price = floatval($data['price']);
        $this->companyId = $data['companyId'];
    }
}

class Company
{
    public $id;
    public $createdAt;
    public $name;
    public $parentId;
    public $cost;
    public $children;
    public function __construct($data)
    {
        $this->id = $data['id'];
        $this->createdAt = $data['createdAt'];
        $this->name = $data['name'];
        $this->parentId = $data['parentId'];
        $this->cost = 0.0;
        $this->children = [];
    }
}

class TestScript
{
    public function execute()
    {
        // Get Company data
        $list_companies = $this->getCompanyData();
        if ($list_companies === null) {
            echo 'Unable to fetch company data from API';
            return;
        }

        // Get Travel data
        $list_travels = $this->getTravelData();
        if ($list_travels === null) {
            echo 'Unable to fetch travel data from API';
            return;
        }

        // Process companies data with key = company id
        $companies = [];
        foreach ($list_companies as $companyData) {
            $company = new Company($companyData);
            $companies[$company->id] = $company;
        }
        
        // Process travels data then find company with key of company id then += the cost
        foreach ($list_travels as $travelData) {
            $travel = new Travel($travelData);
            $companyId = $travel->companyId;
            $companies[$companyId]->cost += $travel->price;
        }

        $nestedCompanies = [];
        foreach ($companies as $company) {
            //No parent
            if ($company->parentId === '0') {
                $nestedCompanies[] = $company;
            } else {
                //Have parent then add to parent as children
                $parentCompany = $companies[$company->parentId];
                $parentCompany->children[] = $company;
            }
        }

        $this->calculateTotalCost($nestedCompanies);
        $result = json_encode($nestedCompanies, JSON_PRETTY_PRINT);
        echo $result;
    }
    private function calculateTotalCost($companies)
    {
        //Calculate cost of children company: From node to root
        foreach ($companies as $company) {
            $this->calculateTotalCost($company->children);
            foreach ($company->children as $childCompany) {
                $company->cost += $childCompany->cost;
            }
        }
    }
    private function getCompanyData()
    {
        $companies = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies';
        $response = file_get_contents($companies);
        return json_decode($response, true);
    }
    private function getTravelData()
    {
        $travels = 'https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels';
        $response = file_get_contents($travels);
        return json_decode($response, true);
    }
}
(new TestScript())->execute();
