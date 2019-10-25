<?php

namespace App\Utils;

class Slugger
{
    private $toLower;

    /*
      Je souhaite conditionner le comportement du slugger via le container de service
      je souhaite imposer une configuration tolower true ou false passé directement a la construction de mon service

      tout comme l'injection de dependance , il est possible aussi de passer de simples parametre fournie par le container de service a ma classe   
     */
    public function __construct(bool $toLower) 
    {
        $this->toLower = $toLower;
    }

    public function slugify($strToConvert) 
    {
        //ici determine si ma chaine va etre en minuscule ou non
        if($this->toLower){
            $strToConvert = strtolower($strToConvert);
        }

        $strConverted = preg_replace( '/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', '-', trim(strip_tags($strToConvert)));

        return $strConverted;
    }
}