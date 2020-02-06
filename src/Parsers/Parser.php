<?php

namespace Evaluator\Parsers;

/**
 * Interface Parser
 * @package Evaluator\Parsers
 */
interface Parser
{
    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data);

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules);

    /**
     * @param int $ruleId
     * @return bool
     */
    public function evaluate($ruleId);

    /**
     * @return array
     */
    public function getFailures();

    /**
     * @return $this
     */
    public function reset();
}
