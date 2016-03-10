DELETE FROM `#__rsform_component_type_fields` WHERE `ComponentTypeId` = 21 ;
DELETE FROM `#__rsform_component_type_fields` WHERE `ComponentTypeId` = 22 ;
DELETE FROM `#__rsform_component_type_fields` WHERE `ComponentTypeId` = 23 ;
DELETE FROM `#__rsform_component_types` WHERE `ComponentTypeId` = 21 ;
DELETE FROM `#__rsform_component_types` WHERE `ComponentTypeId` = 22 ;
DELETE FROM `#__rsform_component_types` WHERE `ComponentTypeId` = 23 ;

DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.gateway' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.api' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.create' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.submit' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.currency' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.thousands' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.decimal' ;
DELETE FROM `#__rsform_config` WHERE `SettingName` = 'bank10.nodecimals' ;

INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.gateway', '');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.api', '');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.create', 'http://10bank.ir//transaction/create');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.submit', 'http://10bank.ir/transaction/submit?id=');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.currency', '');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.thousands', ',');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.decimal', '.');
INSERT IGNORE INTO `#__rsform_config` (`ConfigId`, `SettingName`, `SettingValue`) VALUES ('', 'bank10.nodecimals', '0');

INSERT IGNORE INTO `#__rsform_component_types` (`ComponentTypeId`, `ComponentTypeName`) VALUES (21, 'bank10SingleProduct');
INSERT IGNORE INTO `#__rsform_component_types` (`ComponentTypeId`, `ComponentTypeName`) VALUES (22, 'bank10MultipleProducts');
INSERT IGNORE INTO `#__rsform_component_types` (`ComponentTypeId`, `ComponentTypeName`) VALUES (23, 'bank10Total');

INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'NAME', 'hiddenparam', 'rsfp_Product', 0);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'CAPTION', 'textbox', '', 1);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'PRICE', 'textbox', '', 4);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'SHOW', 'select', 'YES\r\nNO', 3);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'DESCRIPTION', 'textarea', '', 2);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 21, 'COMPONENTTYPE', 'hidden', '21', 0);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 23, 'NAME', 'textbox', '', 0);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 23, 'CAPTION', 'textbox', '', 1);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 23, 'COMPONENTTYPE', 'hidden', '23', 2);

INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'NAME', 'textbox', '', 0);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'CAPTION', 'textbox', '', 1);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'COMPONENTTYPE', 'hidden', '22', 9);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'SIZE', 'textbox', '', 2);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'MULTIPLE', 'select', 'NO\r\nYES', 3);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'ITEMS', 'textarea', '', 5);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'REQUIRED', 'select', 'NO\r\nYES', 6);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'ADDITIONALATTRIBUTES', 'textarea', '', 7);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'DESCRIPTION', 'textarea', '', 8);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'VALIDATIONMESSAGE', 'textarea', 'INVALIDINPUT', 9);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'VIEW_TYPE', 'select', 'DROPDOWN\r\nCHECKBOX', 4);
INSERT IGNORE INTO `#__rsform_component_type_fields` (`ComponentTypeFieldId`, `ComponentTypeId`, `FieldName`, `FieldType`, `FieldValues`, `Ordering`) VALUES('', 22, 'FLOW', 'select', 'HORIZONTAL\r\nVERTICAL', 3);