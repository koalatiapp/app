<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserChangeEmailType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('currentPassword', PasswordType::class, [
				'label' => 'user_settings.security.email.form.current_password',
				'attr' => ['class' => 'medium'],
				'mapped' => false,
				'constraints' => [
					new UserPassword([
						'message' => 'The password you entered did not match your current password.',
					]),
				],
			])
			->add('email', EmailType::class, [
				'label' => 'user_settings.security.email.form.email',
				'attr' => ['class' => 'medium'],
			])
			->add('save', SubmitType::class, [
				'label' => 'user_settings.security.email.form.submit_label',
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
