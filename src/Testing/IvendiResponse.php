<?php

declare(strict_types=1);

namespace NorthBees\IvendiApi\Testing;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;

/**
 * Builds realistic iVendi Connect response `data` for tests.
 */
final class IvendiResponse
{
    /**
     * Quote config data.
     *
     * @param  list<array<string, mixed>>  $products  see configProduct()
     * @return array<string, mixed>
     */
    public static function quoteConfig(array $products = []): array
    {
        return [
            'responseId' => 'config-response',
            'errors' => [],
            'hasErrors' => false,
            'results' => $products === [] ? [self::configProduct('HP'), self::configProduct('PCP')] : $products,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function configProduct(string $facilityType = 'HP', array $overrides = []): array
    {
        return array_replace_recursive([
            'facilityType' => $facilityType,
            'productName' => $facilityType === 'PCP' ? 'Advantage PCP' : 'Advantage HP',
            'productCode' => 'ADV'.$facilityType,
            'deposit' => ['min' => 0, 'max' => 10000, 'default' => 1000],
            'annualMileage' => ['min' => 6000, 'max' => 30000, 'default' => 10000, 'values' => [0]],
            'term' => ['min' => 12, 'max' => 60, 'default' => 48, 'values' => [24, 36, 48, 60]],
        ], $overrides);
    }

    /**
     * Quotes data, with one product request and one asset result.
     *
     * @param  list<array<string, mixed>>  $productGroups  see productGroup()
     * @return array<string, mixed>
     */
    public static function quotes(array $productGroups = [], string $regulatoryText = ''): array
    {
        return [
            'errors' => null,
            'hasErrors' => false,
            'hasQuoteResults' => true,
            'quotedResultsId' => 'quoted-results-id',
            'quoteeContent' => ['commissionDisclosure' => '', 'regulatoryText' => $regulatoryText],
            'quoteResults' => [[
                'errors' => null,
                'hasErrors' => false,
                'results' => [[
                    'asset' => ['requestedTerm' => 48, 'requestedDeposit' => 1000, 'requestedAnnualDistance' => 10000],
                    'errors' => null,
                    'hasErrors' => false,
                    'productGroups' => $productGroups === [] ? [self::productGroup('HP'), self::productGroup('PCP')] : $productGroups,
                ]],
            ]],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $productQuotes  see productQuote()
     * @return array<string, mixed>
     */
    public static function productGroup(string $facilityType = 'HP', array $productQuotes = []): array
    {
        return [
            'facilityType' => $facilityType,
            'errors' => null,
            'hasErrors' => false,
            'productQuotes' => $productQuotes === [] ? [self::productQuote($facilityType)] : $productQuotes,
        ];
    }

    /**
     * @param  array<string, mixed>  $figures  overrides for the figures
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function productQuote(string $facilityType = 'HP', array $figures = [], array $overrides = []): array
    {
        $isPcp = $facilityType === 'PCP';

        return array_replace_recursive([
            'quoteId' => 'quote-'.strtolower($facilityType),
            'productId' => 'product-'.strtolower($facilityType),
            'productName' => $isPcp ? 'Santander PCP' : 'Blackhorse HP',
            'productCode' => $facilityType,
            'quoteeProductCode' => 'D'.$facilityType,
            'funderCode' => $isPcp ? 'SAN' : 'BLA',
            'figures' => array_replace_recursive([
                'acceptanceFee' => 0,
                'advance' => 14000.0,
                'apr' => 9.9,
                'asset' => [
                    'annualDistanceQuoted' => 10000,
                    'cashDeposit' => 1000.0,
                    'chargePerOverDistanceUnit' => $isPcp ? 8.0 : 0.0,
                    'adjustedTerm' => false,
                    'adjustedDistance' => false,
                ],
                'balloon' => $isPcp ? 6000.0 : 0.0,
                'finalPayment' => $isPcp ? 6010.0 : 349.99,
                'firstPayment' => 349.99,
                'interestCharges' => 2799.52,
                'interestRate' => 5.12,
                'numberOfRegularPayments' => $isPcp ? 47 : 48,
                'optionToPurchaseFee' => 10.0,
                'regularPayment' => $isPcp ? 249.5 : 349.99,
                'term' => 48,
                'termQuoted' => 48,
                'totalCashPrice' => 15000.0,
                'totalCharges' => 2799.52,
                'totalDeposit' => 1000.0,
                'totalPayable' => 17799.52,
            ], $figures),
            'errors' => ['hasPrivateErrors' => false, 'hasPublicErrors' => false, 'privateErrors' => [], 'publicErrors' => []],
            'hasErrors' => false,
            'warnings' => ['hasPublicWarnings' => false, 'publicWarnings' => []],
        ], $overrides);
    }

    /**
     * Payment search data.
     *
     * @param  list<array<string, mixed>>  $rows  see paymentRow() and paymentGrid()
     * @return array<string, mixed>
     */
    public static function paymentSearch(array $rows): array
    {
        return ['financeProductResults' => $rows];
    }

    /**
     * @param  array<string, float>  $payments  product key => payment
     * @return array<string, mixed>
     */
    public static function paymentRow(int $term, int|float $deposit, int $annualMileage, array $payments): array
    {
        return [
            'term' => $term,
            'annualMileage' => $annualMileage,
            'deposits' => $deposit,
            'productResults' => array_map(
                fn (string $key, float $payment): array => ['key' => $key, 'payment' => $payment, 'displayName' => $key === 'PCP' ? 'Personal Contract Plan' : 'Hire Purchase'],
                array_keys($payments),
                $payments,
            ),
        ];
    }

    /**
     * Payment search data for a full matrix, with payments from a callback.
     *
     * @param  list<int>  $terms
     * @param  list<int|float>  $deposits
     * @param  list<int>  $annualMileages
     * @param  list<string>  $products
     * @param  (Closure(string, int, int|float, int): ?float)|null  $payment  (product, term, deposit, mileage) => payment, null to omit
     * @return array<string, mixed>
     */
    public static function paymentGrid(array $terms, array $deposits, array $annualMileages, array $products = ['HP', 'PCP'], ?Closure $payment = null): array
    {
        $payment ??= fn (string $product, int $term, int|float $deposit, int $mileage): float => round(12000 / $term - $deposit / 100 + $mileage / 1000 - ($product === 'PCP' ? 60 : 0), 2);
        $rows = [];

        foreach ($terms as $term) {
            foreach ($deposits as $deposit) {
                foreach ($annualMileages as $mileage) {
                    $payments = [];

                    foreach ($products as $product) {
                        if (($amount = $payment($product, $term, $deposit, $mileage)) !== null) {
                            $payments[$product] = $amount;
                        }
                    }

                    $rows[] = self::paymentRow($term, $deposit, $mileage, $payments);
                }
            }
        }

        return self::paymentSearch($rows);
    }

    /**
     * Representative example data.
     *
     * @param  array<string, mixed>  $figures  overrides for the figures
     * @return array<string, mixed>
     */
    public static function representativeExample(array $figures = []): array
    {
        return [
            'asset' => ['isNew' => false, 'registrationDate' => '2021-04-19', 'termDistance' => 42000],
            'funder' => ['code' => 'BLA', 'name' => 'Blackhorse Ltd'],
            'productQuote' => self::productQuote('HP', $figures),
        ];
    }

    /**
     * An error response in the envelope.
     *
     * @param  string|list<string|array<string, mixed>>  $errors
     */
    public static function error(string|array $errors = 'Invalid request', int $status = 400): PromiseInterface
    {
        return Http::response(['data' => null, 'errors' => is_string($errors) ? [$errors] : $errors], $status);
    }
}
