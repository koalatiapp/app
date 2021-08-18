<?php

namespace App\Form;

use App\Entity\User;
use App\Form\Field\ParagraphType;
use App\Form\Field\SpacerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRegistrationType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('firstName', TextType::class, [
				'label' => 'registration.form.field.firstName',
				'attr' => ['class' => 'medium'],
			])
			->add('email', EmailType::class, [
				'label' => 'registration.form.field.email',
				'attr' => ['class' => 'medium'],
			])
			->add('password', PasswordType::class, [
				'label' => 'registration.form.field.password',
				'attr' => ['class' => 'medium'],
			])
			->add('submit', SubmitType::class, [
				'label' => 'registration.form.submit_label',
			])
			->add('spacer', SpacerType::class, [
				'attr' => ['class' => 'spacer small'],
			])
			->add('policies', ParagraphType::class, [
				'data' => 'registration.form.field.policies',
				'attr' => ['class' => 'text-smaller text-center'],
			])
		;
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
