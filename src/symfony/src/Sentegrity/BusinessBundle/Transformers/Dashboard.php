<?php
namespace Sentegrity\BusinessBundle\Transformers;

class Dashboard implements \JsonSerializable
{
    private $topRisks;
    private $topDeviceIssues;
    private $topUserIssues;
    private $graphData = [];
    
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

    /**
     * @param \stdClass $graphData
     * @param $timestamp
     */
    public function setGraphData($graphData, $timestamp)
    {
        $item = new \stdClass();
        $item->platform      = (int)$graphData['platform'];
        $item->phoneModel    = $graphData['phone_model'];
        $item->userScore     = (float)$graphData['user_score'];
        $item->deviceScore   = (float)$graphData['device_score'];
        $item->trustScore    = (float)$graphData['trust_score'];

        $this->graphData[$timestamp][] = $item;
        $item = null;
        unset($item);
    }
    
    function jsonSerialize()
    {
        return [
            'topRisks'         => $this->topRisks,
            'topDeviceIssues'  => $this->topDeviceIssues,
            'topUserIssues'    => $this->topUserIssues,
            'graphData'        => $this->graphData
        ];
    }
}