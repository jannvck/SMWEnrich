<?php
class PropertiesUtil {
	protected static $propertyIds = array(
		'_hasID' => array(
				'type' => '_txt', // data type, see SMWDataItem
				'label' => 'hasID',
				'show' => true),
		'_hasName' => array(
				'smwTypeID' => 2,
				'label' => 'hasName',
				'show' => true));
	public static function registerProperties() {
		foreach(self::$propertyIds as $propertyId => $property) {
			SMWDIProperty::registerProperty(
					 $propertyId,
					 $property['type'],
					 $property['label'],
					 $property['show']);
			/*
			DIProperty::registerPropertyAlias(
					 $propertyId,
					 $property['alias']);
			 */
		}
		return true;
	}
}
?>
