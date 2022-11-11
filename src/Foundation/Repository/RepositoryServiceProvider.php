<?php

namespace Foundation\Repository;

use Foundation\Console\Artisan\MakeRepositoryCommand;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(MakeRepositoryCommand::class);

        $this->bindRepositorriesToContracts();
    }

    private function bindRepositorriesToContracts()
    {
//        $this->app->bind("Offside\Repo\TeamRepositoryInterface",function(){
//            if(Auth::user()){
//                $team = Auth::user()->team;
//                return new TeamRepository($team);
//            }else{
//                return new TeamRepository(new Team());
//            }
//        });
    }
}
