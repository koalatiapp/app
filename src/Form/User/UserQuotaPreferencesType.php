<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserQuotaPreferencesType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('allowsPageTestsOverQuota', CheckboxType::class, [
				'label' => 'user_settings.quota.settings.allows_page_tests_over_quota.label',
				'help' => 'user_settings.quota.settings.allows_page_tests_over_quota.help',
				'required' => false,
			])
			->add('quotaExceedanceSpendingLimit', NumberType::class, [
				'label' => 'user_settings.quota.settings.quota_exceedance_spending_limit.label',
				'help' => 'user_settings.quota.settings.quota_exceedance_spending_limit.help',
				'attr' => [
					'class' => 'small',
					'placeholder' => 'user_settings.quota.settings.quota_exceedance_spending_limit.placeholder',
					'step' => '0.01',
				],
				'html5' => true,
				'scale' => 2,
				'required' => false,
			])
			->add('save', SubmitType::class, [
				'label' => 'user_settings.quota.settings.update',
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
