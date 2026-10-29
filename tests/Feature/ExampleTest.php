<?php

test('it redirects guest to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
