<?php

namespace Draw\Component\Application\Configuration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Application\Configuration\Entity\Config;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Draw\Contracts\Application\Exception\ConfigurationIsNotAccessibleException;

class DoctrineConfigurationRegistry implements ConfigurationRegistryInterface
{
    /**
     * @var Config[]
     */
    private array $configs = [];

    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $manager = $this->managerRegistry->getManagerForClass(Config::class);

        \assert($manager instanceof EntityManagerInterface);

        return $manager;
    }

    public function get(string $name, $default = null)
    {
        try {
            if (!isset($this->configs[$name])) {
                $this->configs[$name] = $this->find($name);
            } else {
                if (UnitOfWork::STATE_MANAGED !== $this->getEntityManager()->getUnitOfWork()->getEntityState($this->configs[$name])) {
                    unset($this->configs[$name]);

                    return $this->get($name, $default);
                }
                $this->getEntityManager()->refresh($this->configs[$name]);
            }

            return isset($this->configs[$name]) ? $this->configs[$name]->getValue() : $default;
        } catch (ConfigurationIsNotAccessibleException $error) {
            throw $error;
        } catch (\Throwable $throwable) {
            throw new ConfigurationIsNotAccessibleException(previous: $throwable);
        }
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->configs) || null !== $this->find($name);
    }

    public function set(string $name, $value): void
    {
        try {
            $config = $this->find($name);
            if (null === $config) {
                $config = new Config();
                $config->setId($name);
                $this->getEntityManager()->persist($config);
            }

            $config->setValue($value);

            $this->getEntityManager()->flush();
        } catch (ConfigurationIsNotAccessibleException $error) {
            throw $error;
        } catch (\Throwable $throwable) {
            throw new ConfigurationIsNotAccessibleException(previous: $throwable);
        }
    }

    public function delete(string $name): void
    {
        try {
            $config = $this->find($name);
            if ($config) {
                $this->getEntityManager()->remove($config);
                $this->getEntityManager()->flush();
            }
            unset($this->configs[$name]);
        } catch (\Throwable $throwable) {
            throw new ConfigurationIsNotAccessibleException(previous: $throwable);
        }
    }

    private function find(string $name): ?Config
    {
        try {
            return $this->getEntityManager()->find(Config::class, $name);
        } catch (\Throwable $throwable) {
            throw new ConfigurationIsNotAccessibleException(previous: $throwable);
        }
    }
}
