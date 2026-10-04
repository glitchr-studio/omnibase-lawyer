<?php

namespace Base\Lawyer\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class LawyerConfiguration extends AbstractBaseConfiguration
{
    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('roles')->addDefaultsIfNotSet()
                    ->info('The roles omnibase\'s groups give (security.yaml puts the first two under the third).')
                    ->children()
                        ->scalarNode('lawyer')->defaultValue('ROLE_LAWYER')->end()
                        ->scalarNode('secretary')->defaultValue('ROLE_SECRETARY')->end()
                        ->scalarNode('staff')->defaultValue('ROLE_STAFF')->end()
                    ->end()
                ->end()
                ->arrayNode('domains')
                    ->info('The domain names the site answers on, checked against RIN art. 10.5 and listed in the memo for the Ordre. None: the host of the router\'s default address.')
                    ->beforeNormalization()->ifString()->then(static fn (string $v) => array_values(array_filter(array_map('trim', explode(',', $v)))))->end()
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->scalarNode('default_uri')->defaultValue('%env(default::DEFAULT_URI)%')
                    ->info('Where the domain name is read when lawyer.domains is empty.')->end()
                ->arrayNode('mediator')->addDefaultsIfNotSet()
                    ->info('The consumer mediator of the lawyers\' profession, set up by the Conseil national des barreaux.')
                    ->children()
                        ->scalarNode('name')->defaultValue('Médiateur de la consommation de la profession d’avocat')->end()
                        ->scalarNode('address')->defaultValue('180 boulevard Haussmann, 75008 Paris')->end()
                        ->scalarNode('email')->defaultValue('mediateur-conso@mediateur-consommation-avocat.fr')->end()
                        ->scalarNode('url')->defaultValue('https://mediateur-consommation-avocat.fr')->end()
                    ->end()
                ->end()
                ->scalarNode('rules_url')->defaultValue('https://www.cnb.avocat.fr/fr/reglement-interieur-national-de-la-profession-davocat-rin')
                    ->info('The profession\'s rules, linked from the legal notice.')->end()
                ->arrayNode('payment')->addDefaultsIfNotSet()
                    ->info('Paying a fee note online - fees only, never clients\' funds. Needs omnibase/marketplace: without it the link is not shown, whatever is set here.')
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('route')->defaultNull()
                            ->info('The application\'s route where a fee note is paid (the marketplace\'s checkout of a fee note).')->end()
                    ->end()
                ->end()
                ->arrayNode('wording')
                    ->info('Expressions added to the built-in list the texts are checked against.')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
