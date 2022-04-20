<?php

namespace Draw\Component\Application;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;

class DoctrineConfigurationRegistry implements ConfigurationRegistryInterface
{
    /**
     * @var Config[]
     */
    private array $configs = [];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(string $name, $default = null)
    {
        if (!isset($this->configs[$name])) {
            $this->configs[$name] = $this->find($name);
        } else {
            if (UnitOfWork::STATE_MANAGED !== $this->entityManager->getUnitOfWork()->getEntityState($this->configs[$name])) {
                unset($this->configs[$name]);

                return $this->get($name, $default);
            }
            $this->entityManager->refresh($this->configs[$name]);
        }

        return isset($this->configs[$name]) ? $this->configs[$name]->getValue() : $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->configs) || null !== $this->find($name);
    }

    public function set(string $name, $value): void
    {
        $config = $this->find($name);
        if (null === $config) {
            $config = new Config();
            $config->setId($name);
            $this->entityManager->persist($config);
        }

        $config->setValue($value);

        $this->entityManager->flush();
    }

    public function delete(string $name): void
    {
        $config = $this->find($name);
        if ($config) {
            $this->entityManager->remove($config);
            $this->entityManager->flush();
        }
        unset($this->configs[$name]);
    }

    private function find(string $name): ?Config
    {
        return $this->entityManager->find(Config::class, $name);
    }
}
