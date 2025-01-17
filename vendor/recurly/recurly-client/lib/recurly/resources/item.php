<?php
/**
 * This file is automatically created by Recurly's OpenAPI generation process
 * and thus any edits you make by hand will be lost. If you wish to make a
 * change to this file, please create a Github issue explaining the changes you
 * need and we will usher them to the appropriate places.
 */
namespace Recurly\Resources;

use Recurly\RecurlyResource;

// phpcs:disable
class Item extends RecurlyResource
{
    private $_accounting_code;
    private $_avalara_service_type;
    private $_avalara_transaction_type;
    private $_code;
    private $_created_at;
    private $_currencies;
    private $_custom_fields;
    private $_deleted_at;
    private $_description;
    private $_external_sku;
    private $_id;
    private $_liability_gl_account_id;
    private $_name;
    private $_object;
    private $_performance_obligation_id;
    private $_revenue_gl_account_id;
    private $_revenue_schedule_type;
    private $_state;
    private $_tax_code;
    private $_tax_exempt;
    private $_updated_at;

    protected static $array_hints = [
        'setCurrencies' => '\Recurly\Resources\Pricing',
        'setCustomFields' => '\Recurly\Resources\CustomField',
    ];

    
    /**
    * Getter method for the accounting_code attribute.
    * Accounting code for invoice line items.
    *
    * @return ?string
    */
    public function getAccountingCode(): ?string
    {
        return $this->_accounting_code;
    }

    /**
    * Setter method for the accounting_code attribute.
    *
    * @param string $accounting_code
    *
    * @return void
    */
    public function setAccountingCode(string $accounting_code): void
    {
        $this->_accounting_code = $accounting_code;
    }

    /**
    * Getter method for the avalara_service_type attribute.
    * Used by Avalara for Communications taxes. The transaction type in combination with the service type describe how the item is taxed. Refer to [the documentation](https://help.avalara.com/AvaTax_for_Communications/Tax_Calculation/AvaTax_for_Communications_Tax_Engine/Mapping_Resources/TM_00115_AFC_Modules_Corresponding_Transaction_Types) for more available t/s types.
    *
    * @return ?int
    */
    public function getAvalaraServiceType(): ?int
    {
        return $this->_avalara_service_type;
    }

    /**
    * Setter method for the avalara_service_type attribute.
    *
    * @param int $avalara_service_type
    *
    * @return void
    */
    public function setAvalaraServiceType(int $avalara_service_type): void
    {
        $this->_avalara_service_type = $avalara_service_type;
    }

    /**
    * Getter method for the avalara_transaction_type attribute.
    * Used by Avalara for Communications taxes. The transaction type in combination with the service type describe how the item is taxed. Refer to [the documentation](https://help.avalara.com/AvaTax_for_Communications/Tax_Calculation/AvaTax_for_Communications_Tax_Engine/Mapping_Resources/TM_00115_AFC_Modules_Corresponding_Transaction_Types) for more available t/s types.
    *
    * @return ?int
    */
    public function getAvalaraTransactionType(): ?int
    {
        return $this->_avalara_transaction_type;
    }

    /**
    * Setter method for the avalara_transaction_type attribute.
    *
    * @param int $avalara_transaction_type
    *
    * @return void
    */
    public function setAvalaraTransactionType(int $avalara_transaction_type): void
    {
        $this->_avalara_transaction_type = $avalara_transaction_type;
    }

    /**
    * Getter method for the code attribute.
    * Unique code to identify the item.
    *
    * @return ?string
    */
    public function getCode(): ?string
    {
        return $this->_code;
    }

    /**
    * Setter method for the code attribute.
    *
    * @param string $code
    *
    * @return void
    */
    public function setCode(string $code): void
    {
        $this->_code = $code;
    }

    /**
    * Getter method for the created_at attribute.
    * Created at
    *
    * @return ?string
    */
    public function getCreatedAt(): ?string
    {
        return $this->_created_at;
    }

    /**
    * Setter method for the created_at attribute.
    *
    * @param string $created_at
    *
    * @return void
    */
    public function setCreatedAt(string $created_at): void
    {
        $this->_created_at = $created_at;
    }

    /**
    * Getter method for the currencies attribute.
    * Item Pricing
    *
    * @return array
    */
    public function getCurrencies(): array
    {
        return $this->_currencies ?? [] ;
    }

    /**
    * Setter method for the currencies attribute.
    *
    * @param array $currencies
    *
    * @return void
    */
    public function setCurrencies(array $currencies): void
    {
        $this->_currencies = $currencies;
    }

    /**
    * Getter method for the custom_fields attribute.
    * The custom fields will only be altered when they are included in a request. Sending an empty array will not remove any existing values. To remove a field send the name with a null or empty value.
    *
    * @return array
    */
    public function getCustomFields(): array
    {
        return $this->_custom_fields ?? [] ;
    }

    /**
    * Setter method for the custom_fields attribute.
    *
    * @param array $custom_fields
    *
    * @return void
    */
    public function setCustomFields(array $custom_fields): void
    {
        $this->_custom_fields = $custom_fields;
    }

    /**
    * Getter method for the deleted_at attribute.
    * Deleted at
    *
    * @return ?string
    */
    public function getDeletedAt(): ?string
    {
        return $this->_deleted_at;
    }

    /**
    * Setter method for the deleted_at attribute.
    *
    * @param string $deleted_at
    *
    * @return void
    */
    public function setDeletedAt(string $deleted_at): void
    {
        $this->_deleted_at = $deleted_at;
    }

    /**
    * Getter method for the description attribute.
    * Optional, description.
    *
    * @return ?string
    */
    public function getDescription(): ?string
    {
        return $this->_description;
    }

    /**
    * Setter method for the description attribute.
    *
    * @param string $description
    *
    * @return void
    */
    public function setDescription(string $description): void
    {
        $this->_description = $description;
    }

    /**
    * Getter method for the external_sku attribute.
    * Optional, stock keeping unit to link the item to other inventory systems.
    *
    * @return ?string
    */
    public function getExternalSku(): ?string
    {
        return $this->_external_sku;
    }

    /**
    * Setter method for the external_sku attribute.
    *
    * @param string $external_sku
    *
    * @return void
    */
    public function setExternalSku(string $external_sku): void
    {
        $this->_external_sku = $external_sku;
    }

    /**
    * Getter method for the id attribute.
    * Item ID
    *
    * @return ?string
    */
    public function getId(): ?string
    {
        return $this->_id;
    }

    /**
    * Setter method for the id attribute.
    *
    * @param string $id
    *
    * @return void
    */
    public function setId(string $id): void
    {
        $this->_id = $id;
    }

    /**
    * Getter method for the liability_gl_account_id attribute.
    * The ID of a general ledger account. General ledger accounts are
only accessible as a part of the Recurly RevRec Standard and
Recurly RevRec Advanced features.

    *
    * @return ?string
    */
    public function getLiabilityGlAccountId(): ?string
    {
        return $this->_liability_gl_account_id;
    }

    /**
    * Setter method for the liability_gl_account_id attribute.
    *
    * @param string $liability_gl_account_id
    *
    * @return void
    */
    public function setLiabilityGlAccountId(string $liability_gl_account_id): void
    {
        $this->_liability_gl_account_id = $liability_gl_account_id;
    }

    /**
    * Getter method for the name attribute.
    * This name describes your item and will appear on the invoice when it's purchased on a one time basis.
    *
    * @return ?string
    */
    public function getName(): ?string
    {
        return $this->_name;
    }

    /**
    * Setter method for the name attribute.
    *
    * @param string $name
    *
    * @return void
    */
    public function setName(string $name): void
    {
        $this->_name = $name;
    }

    /**
    * Getter method for the object attribute.
    * Object type
    *
    * @return ?string
    */
    public function getObject(): ?string
    {
        return $this->_object;
    }

    /**
    * Setter method for the object attribute.
    *
    * @param string $object
    *
    * @return void
    */
    public function setObject(string $object): void
    {
        $this->_object = $object;
    }

    /**
    * Getter method for the performance_obligation_id attribute.
    * The ID of a performance obligation. Performance obligations are
only accessible as a part of the Recurly RevRec Standard and
Recurly RevRec Advanced features.

    *
    * @return ?string
    */
    public function getPerformanceObligationId(): ?string
    {
        return $this->_performance_obligation_id;
    }

    /**
    * Setter method for the performance_obligation_id attribute.
    *
    * @param string $performance_obligation_id
    *
    * @return void
    */
    public function setPerformanceObligationId(string $performance_obligation_id): void
    {
        $this->_performance_obligation_id = $performance_obligation_id;
    }

    /**
    * Getter method for the revenue_gl_account_id attribute.
    * The ID of a general ledger account. General ledger accounts are
only accessible as a part of the Recurly RevRec Standard and
Recurly RevRec Advanced features.

    *
    * @return ?string
    */
    public function getRevenueGlAccountId(): ?string
    {
        return $this->_revenue_gl_account_id;
    }

    /**
    * Setter method for the revenue_gl_account_id attribute.
    *
    * @param string $revenue_gl_account_id
    *
    * @return void
    */
    public function setRevenueGlAccountId(string $revenue_gl_account_id): void
    {
        $this->_revenue_gl_account_id = $revenue_gl_account_id;
    }

    /**
    * Getter method for the revenue_schedule_type attribute.
    * Revenue schedule type
    *
    * @return ?string
    */
    public function getRevenueScheduleType(): ?string
    {
        return $this->_revenue_schedule_type;
    }

    /**
    * Setter method for the revenue_schedule_type attribute.
    *
    * @param string $revenue_schedule_type
    *
    * @return void
    */
    public function setRevenueScheduleType(string $revenue_schedule_type): void
    {
        $this->_revenue_schedule_type = $revenue_schedule_type;
    }

    /**
    * Getter method for the state attribute.
    * The current state of the item.
    *
    * @return ?string
    */
    public function getState(): ?string
    {
        return $this->_state;
    }

    /**
    * Setter method for the state attribute.
    *
    * @param string $state
    *
    * @return void
    */
    public function setState(string $state): void
    {
        $this->_state = $state;
    }

    /**
    * Getter method for the tax_code attribute.
    * Optional field used by Avalara, Vertex, and Recurly's In-the-Box tax solution to determine taxation rules. You can pass in specific tax codes using any of these tax integrations. For Recurly's In-the-Box tax offering you can also choose to instead use simple values of `unknown`, `physical`, or `digital` tax codes.
    *
    * @return ?string
    */
    public function getTaxCode(): ?string
    {
        return $this->_tax_code;
    }

    /**
    * Setter method for the tax_code attribute.
    *
    * @param string $tax_code
    *
    * @return void
    */
    public function setTaxCode(string $tax_code): void
    {
        $this->_tax_code = $tax_code;
    }

    /**
    * Getter method for the tax_exempt attribute.
    * `true` exempts tax on the item, `false` applies tax on the item.
    *
    * @return ?bool
    */
    public function getTaxExempt(): ?bool
    {
        return $this->_tax_exempt;
    }

    /**
    * Setter method for the tax_exempt attribute.
    *
    * @param bool $tax_exempt
    *
    * @return void
    */
    public function setTaxExempt(bool $tax_exempt): void
    {
        $this->_tax_exempt = $tax_exempt;
    }

    /**
    * Getter method for the updated_at attribute.
    * Last updated at
    *
    * @return ?string
    */
    public function getUpdatedAt(): ?string
    {
        return $this->_updated_at;
    }

    /**
    * Setter method for the updated_at attribute.
    *
    * @param string $updated_at
    *
    * @return void
    */
    public function setUpdatedAt(string $updated_at): void
    {
        $this->_updated_at = $updated_at;
    }
}