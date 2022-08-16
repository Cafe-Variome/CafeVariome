<?php namespace App\Libraries\CafeVariome\Database;

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\SettingFactory;

/**
 * SettingAdapter.php
 * Created 21/07/2022
 *
 * This class offers CRUD operation for Setting.
 * @author Mehdi Mehtarizadeh
 */

class SettingAdapter extends BaseAdapter
{
	/**
	 * @inheritDoc
	 */
	protected static string $table = 'settings';

	/**
	 * @inheritDoc
	 */
	protected static string $key = 'id';

	private static ?SettingAdapter $singletonInstance = null;

	private array $settings;

	private function __construct()
	{
		parent::__construct();
		$this->settings = $this->ReadAll();

	}

	/**
	 * @return array
	 */
	public function ReadAll(): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->key] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public function ReadByGroup(string $group): array
	{
		$this->CompileSelect();
		$this->CompileJoin();
		$this->builder->where('group', $group);
		$results = $this->builder->get()->getResult();

		$entities = [];
		for($c = 0; $c < count($results); $c++)
		{
			$entities[$results[$c]->{static::$key}] = $this->binding != null ? $this->BindTo($results[$c]) : $this->toEntity($results[$c]);
		}

		return $entities;
	}

	public static function GetSingletonInstance(): self
	{
		if (self::$singletonInstance == null)
		{
			self::$singletonInstance = new SettingAdapter();
		}

		return self::$singletonInstance;
	}

	public function GetInstallationKey(): ?string
	{
		return $this->settings['installation_key']->value;
	}

	public function GetAuthServerUrl(): ?string
	{
		return $this->settings['auth_server']->value;
	}

	public function GetElasticSearchUri(): ?string
	{
		return $this->settings['elastic_url']->value;
	}

	public function GetNeo4JUserName(): ?string
	{
		return $this->settings['neo4j_username']->value;
	}

	public function GetNeo4JPassword(): ?string
	{
		return $this->settings['neo4j_password']->value;
	}

	public function GetNeo4JUri(): ?string
	{
		return $this->settings['neo4j_server']->value;
	}

	public function GetNeo4JPort(): ?string
	{
		return $this->settings['neo4j_port']->value;
	}

	public function GetSiteTitle(): ?string
	{
		return $this->settings['site_title']->value;
	}

	public function GetHPOAutoCompleteURL(): ?string
	{
		return $this->settings['hpo_autocomplete_url']->value;
	}

	public function GetORPHAAutoCompleteURL(): ?string
	{
		return $this->settings['orpha_autocomplete_url']->value;
	}

	public function GetSNOMEDAutoCompleteURL(): ?string
	{
		return $this->settings['snomed_autocomplete_url']->value;
	}

	public function GetGeneAutoCompleteURL(): ?string
	{
		return $this->settings['gene_autocomplete_url']->value;
	}

	public function GetHeaderImage(): ?string
	{
		return $this->settings['logo']->value;
	}

	/**
	 * Converts general PHP objects to a Setting object.
	 * @param object|null $object
	 * @return IEntity
	 * @throws \Exception
	 */
	public function toEntity(?object $object): IEntity
	{
		$settingFactory = new SettingFactory();
		return $settingFactory->GetInstance($object);
	}
}
