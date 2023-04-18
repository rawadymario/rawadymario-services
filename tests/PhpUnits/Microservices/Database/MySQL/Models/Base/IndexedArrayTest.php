<?php
	namespace DigitalSplash\Tests\Database\MySQL\Models\Base;

    use DigitalSplash\Database\MySQL\Helpers\QueryBuilder;
    use DigitalSplash\Database\MySQL\Models\Base\IndexedArray;
    use DigitalSplash\Database\MySQL\Models\Binds;
	use DigitalSplash\Exceptions\NotEmptyParamException;
	use DigitalSplash\Language\Helpers\Translate;
	use DigitalSplash\Helpers\Helper;
    use PHPUnit\Framework\TestCase;

    class IndexedArrayTest extends TestCase {

        public function constructorThrowsProvider(
        ): array {
            return [
                'empty implodeValue' => [
                    'implodeValue' => '',
                    'statementPrefix' => 'test',
                    'expectExeption' => NotEmptyParamException::class,
                    'expectExeptionMessage' => Translate::TranslateString("exception.NotEmptyParam", null, [
                        "::params::" => 'implodeValue'
                    ])
                ]
            ];
        }

        /**
         * @dataProvider constructorThrowsProvider
         */
        public function testConstructorThrows(
            string $implodeValue,
            string $statementPrefix,
            string $expectExeption,
            string $expectExeptionMessage
        ): void {

            $this->expectException($expectExeption);
            $this->expectExceptionMessage($expectExeptionMessage);

            $indexedArray = new IndexedArray($implodeValue, $statementPrefix);
        }

    }