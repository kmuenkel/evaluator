<?php

namespace Evaluator\Parsers;

/**
 * Class ConfigParser
 * @package Rules\Parsers
 */
class ConfigParser implements Parser
{
    const FIRST_VALUE = 1;
    const SECOND_VALUE = 2;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $failures = [];

    /**
     * RuleParser constructor.
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data = [], array $rules = [])
    {
        $this->setData($data);
        $this->setRules($rules);
    }

    /**
     * {@inheritDoc}
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($ruleId)
    {
        $rule = $this->rules[$ruleId];
        $value1 = $this->getValue($rule, self::FIRST_VALUE);
        $value2 = $this->getValue($rule, self::SECOND_VALUE);
        $comparator = $rule['comparator'] ?? '==';

        switch ($comparator) {
            case '==':
                $success = $value1 == $value2;

                break;
            case '===':
                $success = $value1 === $value2;

                break;
            case '!=':
                $success = $value1 != $value2;

                break;
            case '!==':
                $success = $value1 !== $value2;

                break;
            case '>':
                $success = $value1 > $value2;

                break;
            case '<':
                $success = $value1 < $value2;

                break;
            case 'in':
                $success = in_array($value1, $value2);

                break;
            case 'not in':
                $success = !in_array($value1, $value2);

                break;
            case 'and':
            case '&&':
                $success = $value1 && $value2;

                break;
            case 'or':
            case '||':
                $success = $value1 || $value2;

                break;
            case 'regex':
                $success = preg_match($value2, $value1);

                break;

            case 'all':
                $success = true;
                if (!$value2) {
                    foreach ($value1 as $rule) {
                        if (!($success &= $this->evaluate($rule))) {
                            break;
                        }
                    }
                }

                $success |= count(array_intersect($value1, $value2)) == count($value1);

                break;
            case 'any':
                $success = false;
                if (!$value2) {
                    foreach ($value1 as $rule) {
                        if ($success |= $this->evaluate($rule)) {
                            break;
                        }
                    }
                }

                $success |= (bool)count(array_intersect($value1, $value2));

                break;
            case 'none':
                $success = true;
                if (!$value2) {
                    foreach ($value1 as $rule) {
                        if (!($success |= !$this->evaluate($rule))) {
                            break;
                        }
                    }
                }

                $success = empty(array_intersect($value1, $value2));

                break;
            case 'passes':
                $success = $value2($value1, $this->data);

                break;
            default:
                $success = false;
        }

        if (!$success) {
            $this->failures[$ruleId] = [
                'rule' => $rule,
                'values' => [
                    'value1' => $value1,
                    'value2' => $value2
                ]
            ];
        }

        return $success;
    }

    /**
     * @param string[] $rule
     * @param int $set
     * @return array|bool|mixed
     */
    protected function getValue($rule, $set)
    {
        $value = $rule["value$set"] ?? null;
        $reference = $rule["reference$set"] ?? null;
        $child = $rule["child$set"] ?? null;

        if ($reference) {
            $value = array_match_recursive($this->data, $reference);
            if (strpos($reference, '*') === false) {
                $value = current($value) ?: null;
            }
        }

        if ($child) {
            $value = $this->evaluate($child);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * {@inheritDoc}
     */
    public function reset()
    {
        $this->failures = [];

        return $this;
    }
}
