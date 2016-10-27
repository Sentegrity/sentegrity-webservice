<?php
namespace Sentegrity\BusinessBundle\Transformers;

class GraphData implements \JsonSerializable
{
    private $graphData = [];

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
        $item->user          = $graphData['user_activation_id'];

        $this->graphData[$timestamp][] = $item;
        $item = null;
        unset($item);
    }
    
    function jsonSerialize()
    {
        return [
            'graphData' => $this->graphData
        ];
    }
}