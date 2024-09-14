<?php

//TODO: validálás
Route::any('simplecrud/{eloquentClass}/xhr', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'indexXhr'])->name('simplecrudIndexXhr');
Route::any('simplecrud/{eloquentClass}/select2xhr', [\DelocalZrt\SimpleCrud\Controllers\XhrController::class, 'select2xhr'])->name('xhrSelect2');

Route::group(['middleware' => ['web']], function () {

    Route::get('simplecrud/{eloquentClass}', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'index'])->name('simplecrud-index');
    Route::get('simplecrud/{eloquentClass}/{id}/update', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'update'])->name('simplecrud-update');
    Route::get('simplecrud/{eloquentClass}/create', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'create'])->name('simplecrud-create');
    Route::post('simplecrud/{eloquentClass}/{id}/update', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'updatePost']);
    Route::post('simplecrud/{eloquentClass}/create', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'createPost']);

    Route::post('simplecrud/{eloquentClass}/delete', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'delete'])->name('simplecrud-delete');

    Route::get('simplecrud/{eloquentClass}/{id}', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'show'])->name('simplecrud-show');
    Route::get('simplecrud/{eloquentClass}/{id}/file', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'getFileByPath'])->name('simplecrud-file');
    Route::get('simplecrud/{eloquentClass}/{id}/image', [\DelocalZrt\SimpleCrud\Controllers\IndexController::class, 'showImageByPath'])->name('simplecrud-image');



});


