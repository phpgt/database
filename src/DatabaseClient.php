<?php
namespace Gt\Database;

use Gt\Database\Connection\DefaultSettings;
use Gt\Database\Connection\Driver;
use Gt\Database\Connection\Settings;
use Gt\Database\Connection\SettingsInterface;
use Gt\Database\Query\QueryCollectionFactory;
use Gt\Database\Query\QueryCollectionInterface;

/**
 * The DatabaseClient stores the factory for creating QueryCollections, and an
 * associative array of connection settings, allowing for multiple database
 * connections. If only one database connection is required, a name is not
 * required as the default name will be used.
 */
class DatabaseClient implements DatabaseClientInterface {

/** @var QueryCollectionFactory */
private $queryCollectionFactory;
/** @var \Gt\Database\Connection\Driver[] */
private $driverArray;

public function __construct(SettingsInterface...$connectionSettings) {
	if(empty($connectionSettings)) {
		$connectionSettings[DefaultSettings::DEFAULT_NAME]
			= new DefaultSettings();
	}

	$this->storeConnectionDriversFromSettings($connectionSettings);

	$this->queryCollectionFactory = new QueryCollectionFactory(
		$connectionSettings->getBaseDirectory());
}

private function storeConnectionDriversFromSettings(array $settingsArray) {
	foreach ($settingsArray as $settings) {
		$connectionName = $settings->getConnectionName();
		$this->driverArray[$connectionName] = new Driver($settings);
	}
}

/**
 * Synonym for ArrayAccess::offsetGet
 */
public function queryCollection(
string $queryCollectionName,
string $connectionName = DefaultSettings::DEFAULT_NAME)
:QueryCollectionInterface {
	$driver = $this->driverArray[$connectionName];

	return $this->queryCollectionFactory->create(
		$queryCollectionName,
		$driver
	);
}

public function offsetExists($offset) {
	return $this->queryCollectionFactory->directoryExists($offset);
}

public function offsetGet($offset) {
	return $this->queryCollection($offset);
}

public function offsetSet($offset, $value) {
	throw new ReadOnlyArrayAccessException(self::class);
}

public function offsetUnset($offset) {
	throw new ReadOnlyArrayAccessException(self::class);
}

}#