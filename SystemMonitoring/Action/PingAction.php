<?php

namespace Draw\Component\Application\SystemMonitoring\Action;

use Draw\Component\Application\SystemMonitoring\Status;
use Draw\Component\Application\SystemMonitoring\System;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PingAction
{
    public function __invoke(System $system, string $context = 'ping'): JsonResponse
    {
        $monitoringResult = $system->getServiceStatuses($context);

        switch ($monitoringResult->getStatus()) {
            default:
            case Status::OK:
                $httpStatus = Response::HTTP_OK;
                break;
            case Status::ERROR:
                $httpStatus = Response::HTTP_BAD_GATEWAY;
                break;
            case Status::UNKNOWN:
                $httpStatus = Response::HTTP_MULTI_STATUS;
                break;
        }

        $data = [];
        foreach ($monitoringResult->getServiceStatuses() as $name => $serviceStatuses) {
            $serviceInformation = [
                'name' => $name,
            ];
            foreach ($serviceStatuses as $serviceStatus) {
                $errors = Status::ERROR === $serviceStatus->getStatus() ? ['errors' => $serviceStatus->getErrors()] : [];

                $serviceInformation['subSystems'][] = [
                    'name' => $serviceStatus->getName(),
                    'status' => $serviceStatus->getStatus()->value,
                    ...$errors,
                ];
            }

            $data[] = $serviceInformation;
        }

        return new JsonResponse(
            json_encode(
                ['context' => $context, 'services' => $data],
                \JSON_PRETTY_PRINT
            ),
            $httpStatus,
            json: true
        );
    }
}
