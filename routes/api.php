// SQL 리소스 관리 라우트
$router->post('/resources/sql', 'ResourceController@uploadSQL');
$router->get('/resources/sql', 'ResourceController@listSQL');
$router->get('/resources/sql/:name', 'ResourceController@getSQL');
$router->put('/resources/sql/:name', 'ResourceController@updateSQL');
$router->delete('/resources/sql/:name', 'ResourceController@deleteSQL'); 