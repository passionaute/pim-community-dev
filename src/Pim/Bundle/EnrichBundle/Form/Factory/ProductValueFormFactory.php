<?php

namespace Pim\Bundle\EnrichBundle\Form\Factory;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Event\BuildProductValueFormEvent;
use Pim\Bundle\EnrichBundle\EnrichEvents;

/**
 * Build the product value form
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueFormFactory
{
    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var AttributeTypeFactory */
    protected $attributeTypeFactory;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor
     *
     * @param FormFactoryInterface     $factory
     * @param AttributeTypeFactory     $attTypeFactory
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        FormFactoryInterface $factory,
        AttributeTypeFactory $attTypeFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->formFactory = $factory;
        $this->attributeTypeFactory = $attTypeFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ProductValueInterface $value
     *
     * @return FormInterface
     */
    public function buildProductValueForm(ProductValueInterface $value)
    {
        $attributeTypeAlias = $value->getAttribute()->getAttributeType();
        $attributeType = $this->attributeTypeFactory->get($attributeTypeAlias);

        $name    = $attributeType->prepareValueFormName($value);
        $type    = $attributeType->prepareValueFormAlias($value);
        $data    = $attributeType->prepareValueFormData($value);
        $options = array_merge(
            $attributeType->prepareValueFormConstraints($value),
            $attributeType->prepareValueFormOptions($value)
        );

        $event = new BuildProductValueFormEvent($value, $type, $data, $options);
        $this->eventDispatcher->dispatch(EnrichEvents::CREATE_PRODUCT_VALUE_FORM, $event);

        $valueForm = $this->formFactory->createNamed($name, $type, $data, $options);

        return $valueForm;
    }
}
