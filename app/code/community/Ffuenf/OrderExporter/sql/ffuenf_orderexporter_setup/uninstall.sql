-- add table prefix if you have one
DELETE FROM core_config_data WHERE path like 'ffuenf_orderexporter/%';
DELETE FROM core_config_data WHERE path = 'advanced/modules_disable_output/Ffuenf_OrderExporter';
DELETE FROM core_resource WHERE code = 'ffuenf_orderexporter_setup';