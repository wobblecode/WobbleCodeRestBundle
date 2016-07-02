<?php

namespace Tests\Fixtures\FooBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class TaskType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('priority')
            ->add('completed')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'    => false,
            'render_fieldset'    => false,
            'label_render'       => false,
            'show_legend'        => false,
            'data_class'         => 'Tests\Fixtures\FooBundle\Model\Task'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'task';
    }
}
