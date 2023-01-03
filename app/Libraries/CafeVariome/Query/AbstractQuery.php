<?php namespace App\Libraries\CafeVariome\Query;

/**
 * AbstractQuery.php
 * Created 13/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use App\Libraries\CafeVariome\Entities\Source;
use App\Libraries\CafeVariome\Factory\AttributeAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueAdapterFactory;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
use Elasticsearch\ClientBuilder;

abstract class AbstractQuery
{

	public abstract function Execute(array $clause, Source $source);

	protected function getNeo4JInstance(): Neo4J
	{
		return new Neo4J();
	}

	protected function getESInstance(): \Elasticsearch\Client
	{
		$setting = CafeVariome::Settings();

		$hosts = array($setting->GetElasticSearchUri());
		return ClientBuilder::create()->setHosts($hosts)->build();
	}

	protected function GetESIndexPrefix(): string
	{
		return ElasticsearchHelper::GetIndexPrefix();
	}

	protected function GetESIndexName(int $source_id): string
	{
		return ElasticsearchHelper::GetSourceIndexName($source_id);
	}

	protected function getAttribute(string $attribute, int $source_id)
	{
		// If attribute exists, return it.
		$attributeAdapter = (new AttributeAdapterFactory())->GetInstance();
		$attribute = $attributeAdapter->ReadByNameAndSourceId($attribute, $source_id);
		if (!$attribute->isNull())
		{
			return $attribute;
		}

		// If attribute mapping exists, return the corresponding attribute.
		return $attributeAdapter->ReadByMappingNameAndSourceId($attribute, $source_id);
	}

	protected function getValue(string $value, int $attribute_id)
	{
		//If value exists, return it.
		$valueAdapter = (new ValueAdapterFactory())->GetInstance();
		$valueObj = $valueAdapter->ReadByNameAndAttributeId($value, $attribute_id);
		if (!$valueObj->isNull())
		{
			return $valueObj;
		}

		// If value mapping exists, return the corresponding value.
		return $valueAdapter->ReadByMappingNameAndAttributeId($value, $attribute_id);
	}
}
