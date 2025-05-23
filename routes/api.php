// SQL 리소스 관리 라우트
$router->post('/resources/sql', 'ResourceController@uploadSQL');
$router->get('/resources/sql', 'ResourceController@listSQL');
$router->get('/resources/sql/:name', 'ResourceController@getSQL');
$router->put('/resources/sql/:name', 'ResourceController@updateSQL');
$router->delete('/resources/sql/:name', 'ResourceController@deleteSQL');

// Comment routes
$router->post('/comments', 'CommentController@create');
$router->put('/comments/{id}', 'CommentController@update');
$router->delete('/comments/{id}', 'CommentController@delete');
$router->get('/resources/{resourceId}/comments', 'CommentController@getByResource');
$router->get('/comments/{parentId}/replies', 'CommentController@getReplies');
$router->post('/comments/{id}/report', 'CommentController@report');
$router->post('/comments/{id}/block', 'CommentController@block');
$router->post('/comments/{id}/reactions', 'CommentController@addReaction');
$router->delete('/comments/{id}/reactions', 'CommentController@removeReaction'); 