<?php

namespace App\Http\Controllers\Home\v3;

use App\Http\Controllers\HomeController;
use App\Models\ToolsModel;
use App\Models\ToolsPlateModel;

class ToolsController extends HomeController
{
    public function toolsPlateList()
    {
        $tools_plate = ToolsPlateModel::query()->where(['user_id'=>$this->home_user_id])->get();

        return $this->success('',$tools_plate);
    }

    public function toolsList()
    {
        $tools = ToolsModel::query()->where(['user_id'=>$this->home_user_id])->get();

        return $this->success('',$tools);
    }
}
