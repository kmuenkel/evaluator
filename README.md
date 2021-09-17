# evaluator
Check a given data-set against a recursive rule-set

You begin with a config array that may or may not originate from a database. There are only three dataponts to each "rule".
- First Value
- Comparison operator
- Second Value

The key name for the first and second values indicates the nature of these values.
- `reference1/reference2` indicates that the content will posses a dot-notation reference to a datapoint within the array of data being evaluated.
- `value1/value2` will expect to hold a static value that should be taken at face value.
- `child1/child2` means the "value" would be the boolean result of a nested "rule".

Through the dot-notation references and support of 'child' rules, both the data and the conditions support infinite complexity. But being a flat 3-point 'rule' record, this makes it conducive to the rules being managed through a database instead of a config file. While such a parser doesn't exist yet, all you'd need to do is ensure that your `DBParser` class implements the same `Parser` interface as the current `ConfigParser`.

The comparison operators, keyed as `comparator`, may be any of the following:

- '==': Value 1 equals value 2
- '===': Value 1 strictly equals value 2
- '!=': Value 1 does not equal value 2
- '!==': Value 1 strictly does not equal value 2
- '>': Value 1 is greater than value 2
- '<': Value 1 is less than value 2
- 'in': Value 1 can be found in an array defined as value 2
- 'not in': Value 1 cannot be found in an array defined as value 2
- 'and': Value 1 and value 2 must resolve to `true`
- '&&': Same as 'and'
- 'or': Value 1 or value 2 must resolve to `true`
- '||': Same as 'or'
- 'regex': Value 1 matches a pattern defined by value 2
- 'all':
  - All elements in an array defined by value 1 must be found in an array defined by value 2
  - **Or** If only one array is present, it must be reference nested rules that all resolve to `true`
- 'any':
  - Any element found in an array defined by value 1 must be found in an array defined by value 2
  - **Or** If only one array is present, it must reerence nested rules, for which at least one resolves to `true`
- 'none':
  - None of the elements in an array defined by value 1 may be present in an array defined by value 2
  - **Or** If only one array is present, it must reference nested rules that all resolve to `false` (or be empty)
- 'passes': Value 1 and the entire dataset being evaluated get passed to a closure defgined by value 2, the output of which must resolve to `true`. This allows for custom rules behavior to be added.

###Sample Usage###

`ruleConfig.php`
```php
return [
    'isKevin' => [
        'reference1' => 'name.first',
        'comparator' => '==',
        'value2' => 'Kevin'
    ],
    'inCarolinas' => [
        'reference1' => 'address.state',
        'comparator => 'in',
        'value2' => [
            'NC',
            'SC'
        ]
    ],
    'isMe' => [
        'comparator' => 'all',
        'value2' => [
            'isKevin',
            'inCarolinas
        ]
    ]
];
```

`data.json`
```json
{
  "name": {
    "first": "Kevin"
  },
  "address": {
      "state": "NC"
  }
}
```

```php
use Evaluator\Parsers\ConfigParser;

$rules = require_once 'ruleConfig.php';
$data = json_decode(file_get_contents("data.json"), true);
$dotNotation = dotNotation($data); //Conversion function not included (yet)

$result = (new ConfigParser($data, $rules))->evaluate('isMe'); // true
```
