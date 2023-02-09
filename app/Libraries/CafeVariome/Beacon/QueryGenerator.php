<?php namespace App\Libraries\CafeVariome\Beacon;

/**
 * QueryGenerator.php
 * Created: 06/12/2022
 * @author Mehdi Mehtarizadeh
 *
 * This class handles generating beacon queries from internal Cafe Variome queries.
 * @see https://beacon-project.io/
 * @see https://github.com/rini21/vp-api-specs-beaconised
 *
 */

class QueryGenerator
{
	public function Generate(array $inputQuery)
	{
		$meta = $this->newMeta();
		$query = $this->newQuery();
		$filters = $this->newFilters();


		if (
			array_key_exists('query', $inputQuery) &&
			array_key_exists('components', $inputQuery['query']) &&
			is_array($inputQuery['query']['components'])
		)
		{
			foreach ($inputQuery['query']['components'] as $component => $clauses)
			{
				foreach ($clauses as $clause)
				{
					switch (strtolower($component))
					{
						case 'eav':
							$filters[] = $this->newAlphaNumericFilter($clause['attribute'], $clause['operator'], $clause['value']);
							break;
						case 'sim':
							$filters[] = $this->newOntologyFilter(str_replace(':', '_', $clause['ids'][0]));
							break;
						case 'ordo':
							$filters[] = $this->newOntologyFilter($clause['id'][0]);
							break;
					}
				}
			}
		}

		$query['query']['filters'] = $filters;

		return array_merge($meta, $query);
	}

	private function newMeta(): array
	{
		return [
			'$schema' => 'https://json-schema.org/draft/2020-12/schema',
			'meta' => [
				'apiVersion' => Beacon::BEACON_VERSION,
				'beaconId' => Beacon::GetBeaconID(),
				'returnedSchemas' => [
					'entityType' => 'string',
					'schema' => 'string'
				]
			]
		];
	}

	private function newQuery(): array
	{
		return ['query' => ['filters' => null]];
	}

	private function newFilters(): array
	{
		return [];
	}

	private function newAlphaNumericFilter(string $id, string $operator, string $value): array
	{
		return [
			'id' => $id, 'operator' => $operator, 'value' => $value
		];
	}

	private function newOntologyFilter(string $term): array
	{
		return ['id' => $term];
	}
}
