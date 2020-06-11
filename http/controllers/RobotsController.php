<?php declare(strict_types=1);

namespace LeMaX10\MultiSite\Http\Controllers;

use LeMaX10\MultiSite\Models\Site;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RobotsController
 * @package LeMaX10\MultiSite\Http\Controllers
 */
class RobotsController
{
    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        /** @var Site $currentSite */
        $currentSite = currentSite();

        return response($currentSite->getRobotsContent(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8'
        ]);
    }
}
