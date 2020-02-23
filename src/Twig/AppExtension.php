<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\Project;
use App\Entity\UserProject;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    protected $params;

    public function getFunctions()
    {
        return
        [
            new TwigFunction('get_parameter', [$this, 'getParameter'])
        ];
    }

    public function getFilters()
    {
        return
        [
            new TwigFilter('on_going', [$this, 'onGoing']),
            new TwigFilter('on_delivered', [$this, 'onDelivered']),
            new TwigFilter('rentability', [$this, 'rentability']),
            new TwigFilter('production_time_spend', [$this, 'productionTimeSpend']),
            new TwigFilter('fr_month', [$this, 'frMonth']),
            new TwigFilter('pluriel', [$this, 'pluriel']),
        ];
    }

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param string parameter
     * @return string
     */
    public function getParameter($parameter): string
    {
        return $this->params->get($parameter) ?? 'UNKNOWN VARIABLE';
    }

    /**
     * @param Project[] $projects
     * @return Project[]
     */
    public function onGoing(array $projects)
    {
        return array_filter($projects, function(Project $project)
        {
            return is_null($project->getDeliveredOn());
        });
    }

    /**
     * @param Project[] $projects
     * @return Project[]
     */
    public function onDelivered(array $projects)
    {
        return array_filter($projects, function(Project $project)
        {
            return !is_null($project->getDeliveredOn());
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
     * @throws Exception
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
    public function frMonth($month_index)
    {
        $month = array
        (
            'janvier',
            'février',
            'mars',
            'avril',
            'mai',
            'juin',
            'juillet',
            'août',
            'septembre',
            'octobre',
            'novembre',
            'décembre'
        );

        return $month[$month_index - 1];
    }

    /**
     * @param $str
     * @param $i
     * @param $o
     * @param $n
     * @return string
     */
    public function pluriel($str,$i,$o,$n)
    {
        return $str . ($i > 1 ? $o : $n);
    }


}