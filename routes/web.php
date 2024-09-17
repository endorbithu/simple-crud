<?php

//TODO: validálás
Route::any('simplecrud/{eloquentClass}/xhr', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'indexXhr'])->name('simplecrudIndexXhr');
Route::any('simplecrud/{eloquentClass}/select2xhr', [\Endorbit\SimpleCrud\Controllers\XhrController::class, 'select2xhr'])->name('xhrSelect2');

Route::group(['middleware' => ['web']], function () {

    Route::get('simplecrud/{eloquentClass}', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'index'])->name('simplecrud-index');
    Route::get('simplecrud/{eloquentClass}/{id}/update', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'update'])->name('simplecrud-update');
    Route::get('simplecrud/{eloquentClass}/create', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'create'])->name('simplecrud-create');
    Route::post('simplecrud/{eloquentClass}/{id}/update', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'updatePost']);
    Route::post('simplecrud/{eloquentClass}/create', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'createPost']);

    Route::post('simplecrud/{eloquentClass}/delete', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'delete'])->name('simplecrud-delete');

    Route::get('simplecrud/{eloquentClass}/{id}', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'show'])->name('simplecrud-show');
    Route::get('simplecrud/{eloquentClass}/{id}/file', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'getFileByPath'])->name('simplecrud-file');
    Route::get('simplecrud/{eloquentClass}/{id}/image', [\Endorbit\SimpleCrud\Controllers\IndexController::class, 'showImageByPath'])->name('simplecrud-image');



});


