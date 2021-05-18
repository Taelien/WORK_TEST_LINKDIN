<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;


class WorkTestDemo
{
    function array_count_values_of($value, $array) {
        $counts = array_count_values($array);
        if (isset($counts[$value])){
            return $counts[$value];
        }
        else {
            return 0;
        }
    }
    
    function grabLinkedIn()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://api.linkedin.com/v2/learningAssets?q=criteria&assetFilteringCriteria.assetTypes[0]=COURSE&assetFilteringCriteria.locales[0].language=en&assetFilteringCriteria.locales[0].country=US', [
                'auth_bearer' => '', //Key goes here
        ]);
        $content = $response->getContent();
        $ourJson = json_decode($content);

        return $ourJson;
    }

    public function grabSpecificSkillName(string $skillName) : Response 
    {
        $ourJson = $this->grabLinkedIn();
        $ourElements = $ourJson->elements;
        $returnElement = "Skill Not Found";

        foreach($ourElements as $key => $elem){
            foreach($elem->details->classifications as $classification){
                if ($classification->associatedClassification->type == 'SKILL') {
                    if ($classification->associatedClassification->name->value == $skillName){
                        $returnElement = $elem;
                    }
                }
            }
            
        }

        if ($returnElement !== null){
            $returnElement = json_encode($returnElement);
        }
        return new Response(
            '<html><body>'.$returnElement.'</body></html>'
        );
    }

    public function grabUniqueElements() : Response
    {
        $ourJson = $this->grabLinkedIn();
        $ourElements = $ourJson->elements;
        
        $quickList = array();
        $uniqueList = array();

        foreach($ourElements as $key => $elem){
            $skillName = '';
            foreach($elem->details->classifications as $classification){
                if ($classification->associatedClassification->type == 'SKILL') {
                    $skillName = $classification->associatedClassification->name->value;
                }
                $quickList[$key] = $skillName;
            }
            
        }

        $alreadyAddedDuplicates = array(); //I only added this when I realized you might want 1 rendition of the duplicates added. 
        foreach($quickList as $key => $value){
            $numberOf = $this->array_count_values_of($value, $quickList);
            $numberOfDuplicate = $this->array_count_values_of($value, $alreadyAddedDuplicates);
            
            if ($numberOf <= 1){
                $uniqueList[] = $ourElements[$key];
            } 
            else { //I only added this when I realized you might want 1 rendition of the duplicates added. 
                if ($numberOfDuplicate == 0){
                    $uniqueList[] = $ourElements[$key];
                    $alreadyAddedDuplicates[] = $value;
                }

            }
        }
        
        $uniqueList = json_encode($uniqueList);
        return new Response(
            '<html><body>'.$uniqueList.'</body></html>'
        );
    }
}