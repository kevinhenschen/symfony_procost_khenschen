<?php


// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserProject;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('onGoing', [$this, 'onGoing']),
            new TwigFilter('onDelivered', [$this, 'onDelivered']),
            new TwigFilter('rentability', [$this, 'rentability']),
            new TwigFilter('productionTimeSpend', [$this, 'productionTimeSpend']),
            new TwigFilter('frMonth', [$this, 'frMonth']),
            new TwigFilter('pluriel', [$this, 'pluriel']),
        ];
    }


    /**
     * @param Project[] $projects
     * @return Project[]
     */
    public function onGoing(array $projects)
    {
        return array_filter($projects, function($value){
            return is_null($value->getDeliveredOn());
        });
    }

    /**
     * @param Project[] $projects
     * @return Project[]
     */
    public function onDelivered(array $projects)
    {
        return array_filter($projects, function($value){
            return !is_null($value->getDeliveredOn());
        });
    }

    /**
     * @param Project[] $projects
     * @return double
     */
    public function rentability(array $projects)
    {
        $finalPriceProjects = 0;
        $wantedPriceProjects = 0;

        foreach ($projects as $project)
        {
                $finalPriceProjects += $project->getCost();
                $wantedPriceProjects += $project->getPrice();
        }

        return ($wantedPriceProjects * 100) / $finalPriceProjects;
    }

    /**
     * @param UserProject[] $userProjects
     * @return double
     * @throws \Exception
     */
    public function productionTimeSpend(array $userProjects)
    {
        $nbTimeSpend = 0;
        foreach ($userProjects as $userProject)
        {
            $nbTimeSpend += $userProject->getTimeSpent();
        }
        return $nbTimeSpend;
    }

    /**
     * @param integer $month_index
     * @return string
     */
    public function frMonth($month_index){
        $month = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
        return $month[$month_index - 1];
    }

    /**
     * @param $str
     * @param $i
     * @param $o
     * @param $n
     * @return string
     */
    public function pluriel($str,$i,$o,$n){
        return $str . ($i > 1 ? $o : $n);
    }


}