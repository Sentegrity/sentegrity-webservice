<?php
namespace Sentegrity\BusinessBundle\Transformers;


class DashboardTopData implements \JsonSerializable
{
    private $topRisks;
    private $topDeviceIssues;
    private $topUserIssues;

    /**
     * @param mixed $topRisks
     */
    public function setTopRisks($topRisks)
    {
        $this->topRisks = $topRisks;
    }

    /**
     * @param mixed $topDeviceIssues
     */
    public function setTopDeviceIssues($topDeviceIssues)
    {
        $this->topDeviceIssues = $topDeviceIssues;
    }

    /**
     * @param mixed $topUserIssues
     */
    public function setTopUserIssues($topUserIssues)
    {
        $this->topUserIssues = $topUserIssues;
    }

    function jsonSerialize()
    {
        return [
            'topRisks'         => $this->topRisks,
            'topDeviceIssues'  => $this->topDeviceIssues,
            'topUserIssues'    => $this->topUserIssues
        ];
    }
}