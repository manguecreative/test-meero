How to test?
1. php bin/console server:start
2. php bin/console doctrine:schema:update --force
3. php bin/console app:import-orders "path_to_xml_orders"
4. go to localhost:8000/admin

