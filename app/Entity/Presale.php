<?php

declare(strict_types=1);

namespace BeycanPress\TokenicoLite\Entity;

use BeycanPress\TokenicoLite\PluginHero\Entity;
use BeycanPress\TokenicoLite\PluginHero\Helpers;

class Presale extends Entity
{
    /**
     * @var string|null
     */
    private ?string $currencySymbol = null;

    /**
     * @var string|null
     */
    private ?string $networkName = null;

    /**
     * @var int|null
     */
    public ?int $ID;

    /**
     * @param int|null $entityId
     */
    public function __construct(?int $entityId)
    {
        $this->ID = $entityId;

        parent::__construct($entityId);

        // phpcs:ignore
        if ($this->network && 'presale' == $this->post_type) {
            $this->currencySymbol = json_decode($this->network)->nativeCurrency->symbol;
            $this->networkName = json_decode($this->network)->name;
        }
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        $utcTime = strtotime(wp_date("Y-m-d H:i:s", null, new \DateTimeZone('UTC')));
        if (
            strtotime($this->startDate) <= $utcTime &&
            strtotime($this->endDate) >= $utcTime &&
            $this->totalSales < $this->totalSaleLimit
        ) {
            return 'started';
        } elseif (
            strtotime($this->endDate) <= $utcTime ||
            $this->totalSales == $this->totalSaleLimit
        ) {
            return 'ended';
        } else {
            return 'notStarted';
        }
    }

    /**
     * @return string
     */
    public function getNativeCoinPrice(): string
    {
        $url = "https://min-api.cryptocompare.com/data/price";
        $apiUrl =  $url . '?fsym=' . $this->currencySymbol . '&tsyms=USD';
        $convertData = json_decode(file_get_contents($apiUrl));
        return $convertData->USD;
    }

    /**
     * @return string
     */
    public function getSaleUsdBetween(): string
    {
        $nativeCoinPrice = $this->getNativeCoinPrice();
        $totalSalesUsdPrice = Helpers::toFixed($this->totalSales * $nativeCoinPrice, 2);
        $totalSaleLimitUsdPrice = Helpers::toFixed($this->totalSaleLimit * $nativeCoinPrice, 2);

        return "$totalSalesUsdPrice$ - $totalSaleLimitUsdPrice$";
    }

    /**
     * @return string
     */
    public function getStaticSaleUsdBetween(): string
    {
        $totalTokenSaleCount = $this->totalSaleLimit * $this->exchangeRate;
        $totalTokenSoldCount = $this->totalSales * $this->exchangeRate;
        $totalSalesPrice = Helpers::toFixed($this->usdRate * $totalTokenSoldCount, 2);
        $totalSaleLimitPrice = Helpers::toFixed($this->usdRate * $totalTokenSaleCount, 2);
        $totalSalesPrice = Helpers::numberFormat(floatval($totalSalesPrice));
        $totalSaleLimitPrice = Helpers::numberFormat(floatval($totalSaleLimitPrice));

        return "$totalSalesPrice$ - $totalSaleLimitPrice$";
    }

    /**
     * @return string
     */
    public function getStaticSaleTokenBetween(): string
    {
        $tokenSymbol = $this->getTokenSymbol();
        $totalTokenSaleCount = $this->totalSaleLimit * $this->exchangeRate;
        $totalTokenSoldCount = $this->totalSales * $this->exchangeRate;
        $totalSalesPrice = Helpers::toFixed($totalTokenSoldCount, 2);
        $totalSaleLimitPrice = Helpers::toFixed($totalTokenSaleCount, 2);
        $totalSalesPrice = Helpers::numberFormat(floatval($totalSalesPrice));
        $totalSaleLimitPrice = Helpers::numberFormat(floatval($totalSaleLimitPrice));

        return "$totalSalesPrice $tokenSymbol - $totalSaleLimitPrice $tokenSymbol";
    }

    /**
     * @return string
     */
    public function getStaticSaleNativeBetween(): string
    {
        $totalTokenSaleCount = $this->totalSaleLimit * $this->exchangeRate;
        $totalTokenSoldCount = $this->totalSales * $this->exchangeRate;
        $totalSalesPrice = Helpers::toFixed((1 / $this->exchangeRate)  * $totalTokenSoldCount, 2);
        $totalSaleLimitPrice = Helpers::toFixed((1 / $this->exchangeRate)  * $totalTokenSaleCount, 2);
        $totalSalesPrice = Helpers::numberFormat(floatval($totalSalesPrice));
        $totalSaleLimitPrice = Helpers::numberFormat(floatval($totalSaleLimitPrice));

        return "$totalSalesPrice $this->currencySymbol - $totalSaleLimitPrice $this->currencySymbol";
    }

    /**
     * @return string
     */
    public function getStaticNativeRate(): string
    {
        $tokenSymbol = $this->token ? json_decode($this->token)->symbol : null;
        $exchangeRate = Helpers::numberFormat(floatval($this->exchangeRate));
        return '1 ' . $this->currencySymbol . ' = ' . $exchangeRate . ' ' . $tokenSymbol;
    }

    /**
     * @return string
     */
    public function getStaticUsdRate(): string
    {
        $tokenSymbol = $this->token ? json_decode($this->token)->symbol : null;
        return '1 ' . $tokenSymbol . ' = ' . $this->usdRate . ' USD';
    }

    /**
     * @return string
     */
    public function getUsdRate(): string
    {
        $usdPrice = $this->getUsdPrice();
        $tokenSymbol = $this->token ? json_decode($this->token)->symbol : null;
        return '1 ' . $tokenSymbol . ' = ' . $usdPrice . ' USD';
    }

    /**
     * @return string
     */
    public function getUsdPrice(): string
    {
        $nativeCoinPrice = 1 / $this->exchangeRate;
        $usdPrice = $nativeCoinPrice * $this->getNativeCoinPrice();
        preg_match_all('/^[0.]+\d{3}/', Helpers::toString($usdPrice, 18), $matches, PREG_SET_ORDER, 0);
        return $matches[0][0];
    }

    /**
     * @return string
     */
    public function getNetworkName(): string
    {
        return $this->networkName;
    }

    /**
     * @return string
     */
    public function getTotalSaleLimit(): string
    {
        return $this->totalSaleLimit . ' ' . $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getTotalSales(): string
    {
        return $this->totalSales . ' ' . $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getRemainingLimit(): string
    {
        return $this->remainingLimit . ' ' . $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getMinContribution(): string
    {
        return Helpers::numberFormat(floatval($this->minContribution)) . ' ' . $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getMaxContribution(): string
    {
        return Helpers::numberFormat(floatval($this->maxContribution)) . ' ' . $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getNativeCoinSymbol(): string
    {
        return $this->currencySymbol;
    }

    /**
     * @return string
     */
    public function getTokenSymbol(): string
    {
        return $this->token ? json_decode($this->token)->symbol : null;
    }

    /**
     * @return string
     */
    public function getExchangeRate(): string
    {
        return 1 . ' ' . $this->getNativeCoinSymbol() . ' = ' . $this->exchangeRate . ' ' . $this->getTokenSymbol();
    }

    /**
     * @return float|int
     */
    public function calculateMinTokenAmount(): float|int
    {
        return $this->minContribution * $this->exchangeRate;
    }

    /**
     * @return float|int
     */
    public function calculateMaxTokenAmount(): float|int
    {
        return $this->maxContribution * $this->exchangeRate;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return date_i18n(get_option('date_format') . ' H:i', strtotime($this->startDate));
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return date_i18n(get_option('date_format') . ' H:i', strtotime($this->endDate));
    }
}
