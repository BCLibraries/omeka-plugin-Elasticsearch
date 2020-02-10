<?php

class Elasticsearch_Model_SearchConstraintList
{
    /** @var Elasticsearch_Model_SearchConstraint[] */
    private $constraints = [];

    public function __construct()
    {
        // Normalize keys
        $get_array = array_change_key_case($_GET, CASE_LOWER);

        foreach ($get_array as $key => $val) {
            $constraint = new Elasticsearch_Model_SearchConstraint($key);
            if (is_array($val) && $constraint->isMultiParam()) {
                foreach ($val as $entry) {
                    if (is_array($entry) && isset($entry['or'])) {
                        $constraint->addValue($entry['or']);
                    }
                }
            } else {
                $constraint->addValue($val);
            }
            $this->constraints[] = $constraint;
        }
    }

    /**
     * @return Elasticsearch_Model_SearchConstraint[]
     */
    public function getConstraints(): array
    {
        $constraints = [];
        foreach ($this->constraints as $constraint) {
            if ($constraint->getKey() === 'q') {
                array_unshift($constraints, $constraint);
            } else {
                $constraints[] = $constraint;
            }
        }
        return $constraints;
    }

    public function hasSearchConstraints()
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->isSearchParam() && $constraint->hasValues()) {
                return true;
            }
        }
        return false;
    }

    public function __toString(): string
    {
        $query_string_parts = [];

        foreach ($this->constraints as $constraint) {
            if (!$constraint->hasValues()) {
                continue;
            }

            if ($constraint->isMultiParam()) {
                $multiple_value = [];
                foreach ($constraint->getValues() as $value) {
                    $multiple_value[] = ['or' => $value->getValue()];
                }
                $query_string_parts[$constraint->getKey()] = $multiple_value;
            } else {
                $query_string_parts[$constraint->getKey()] = $constraint->getValues()[0]->getValue();
            }

        }

        return http_build_query($query_string_parts);
    }
}