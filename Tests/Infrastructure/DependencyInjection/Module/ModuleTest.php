<?php

declare(strict_types=1);

/*
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

namespace ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use LogicException;
use ParkManager\Module\CoreModule\Infrastructure\DependencyInjection\Module\AbstractParkManagerModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\DoctrineMappingsModule\DoctrineMappingsModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionAbsentModule\ExtensionAbsentModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionAliasNotValidModule\ExtensionAliasNotValidModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionNotValidModule\ExtensionNotValidModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionPresentModule\ExtensionPresentModule;
use ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module\Fixtures\ExtensionPresentModule\Infrastructure\DependencyInjection\DependencyExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ModuleTest extends TestCase
{
    /** @test */
    public function it_get_extension_class_if_present(): void
    {
        $module = new ExtensionPresentModule();

        $this->assertInstanceOf(DependencyExtension::class, $module->getContainerExtension());
    }

    /** @test */
    public function it_ignores_extension_class_if_absent(): void
    {
        $module = new ExtensionAbsentModule();

        $this->assertNull($module->getContainerExtension());
    }

    /** @test */
    public function it_throws_a_LogicException_when_extension_class_is_invalid(): void
    {
        $module = new ExtensionNotValidModule();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must implement Symfony\Component\DependencyInjection\Extension\ExtensionInterface');

        $module->getContainerExtension();
    }

    /** @test */
    public function it_throws_a_LogicException_when_extension_alias_is_not_expected(): void
    {
        $module = new ExtensionAliasNotValidModule();

        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Users will expect the alias of the default extension of a module to be the ' .
            'underscored version of the module name ("extension_alias_not_valid"). ' .
            'You can override "AbstractParkManagerModule::getContainerExtension()" ' .
            'if you want to use "extension_valid_is_not" or another alias.'
        );

        $module->getContainerExtension();
    }

    /** @test */
    public function it_guesses_name_of_the_module(): void
    {
        $module = new GuessedNameModule();

        $this->assertSame('ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module', $module->getNamespace());
        $this->assertSame('GuessedNameModule', $module->getName());
    }

    /** @test */
    public function its_module_can_be_explicitly_provided(): void
    {
        $module = new NamedModule();

        $this->assertSame('ExplicitlyNamedModule', $module->getName());
        $this->assertSame('ParkManager\Module\CoreModule\Tests\Infrastructure\DependencyInjection\Module', $module->getNamespace());
    }

    /** @test */
    public function it_ignored_doctrine_mapping_when_absent(): void
    {
        $containerBuilder = new ContainerBuilder();

        $module = new NamedModule();
        $module->build($containerBuilder);

        self::assertThat(
            $containerBuilder->getCompilerPassConfig()->getPasses(),
            new TraversableNotContains(DoctrineOrmMappingsPass::class, false)
        );
    }

    /** @test */
    public function it_registers_doctrine_mapping_when_present(): void
    {
        $containerBuilder = new ContainerBuilder();

        $module = new DoctrineMappingsModule();
        $module->build($containerBuilder);

        self::assertContains(
            DoctrineOrmMappingsPass::createXmlMappingDriver([
                $module->getPath() . '/Infrastructure/Doctrine/Account/Mapping' => $module->getNamespace() . '\\Domain\\Account',
            ]),
            $containerBuilder->getCompilerPassConfig()->getPasses(),
            '',
            false,
            false,
            true
        );
    }
}

class NamedModule extends AbstractParkManagerModule
{
    public function __construct()
    {
        $this->name = 'ExplicitlyNamedModule';
    }
}

class GuessedNameModule extends AbstractParkManagerModule
{
}
