<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\BatchImportAttendanceController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\DetectAnomaliesController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceRateStatisticsController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceStatisticsController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetCourseSummaryController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\MakeupAttendanceController;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\RecordAttendanceController;
use Tourze\TrainClassroomBundle\Controller\Api\Register\RegisterFormController;
use Tourze\TrainClassroomBundle\Controller\Api\Register\RegisterSubmitController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\BatchCreateScheduleController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\CancelScheduleController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\CreateScheduleController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\GetScheduleDetailController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\GetScheduleListController;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\UpdateScheduleController;
use Tourze\TrainClassroomBundle\Controller\Classroom\CreateClassroomController;

#[AutoconfigureTag(name: 'routing.loader')]
final class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        // Attendance Controllers
        $collection->addCollection($this->controllerLoader->load(RecordAttendanceController::class));
        $collection->addCollection($this->controllerLoader->load(BatchImportAttendanceController::class));
        $collection->addCollection($this->controllerLoader->load(GetAttendanceStatisticsController::class));
        $collection->addCollection($this->controllerLoader->load(GetCourseSummaryController::class));
        $collection->addCollection($this->controllerLoader->load(DetectAnomaliesController::class));
        $collection->addCollection($this->controllerLoader->load(MakeupAttendanceController::class));
        $collection->addCollection($this->controllerLoader->load(GetAttendanceRateStatisticsController::class));
        // Schedule Controllers
        $collection->addCollection($this->controllerLoader->load(CreateScheduleController::class));
        $collection->addCollection($this->controllerLoader->load(BatchCreateScheduleController::class));
        $collection->addCollection($this->controllerLoader->load(UpdateScheduleController::class));
        $collection->addCollection($this->controllerLoader->load(CancelScheduleController::class));
        $collection->addCollection($this->controllerLoader->load(GetScheduleListController::class));
        $collection->addCollection($this->controllerLoader->load(GetScheduleDetailController::class));
        // Register Controllers
        $collection->addCollection($this->controllerLoader->load(RegisterFormController::class));
        $collection->addCollection($this->controllerLoader->load(RegisterSubmitController::class));
        // Classroom Controllers
        $collection->addCollection($this->controllerLoader->load(CreateClassroomController::class));

        return $collection;
    }
}
