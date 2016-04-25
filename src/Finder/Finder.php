<?php

namespace FrozenDinosaur\Finder;

use Symfony\Component\Finder\Finder as BaseFinder;

/**
 * Files Finder.
 */
class Finder
{

    /**
     * Permet de lister l'ensemble des fichiers contenu dans les dossiers sources.
     *
     * @param array $sources    Dossiers sources.
     * @param array $exclude    Dossiers  à exclure.
     * @param array $extensions Extensions des fichiers à retourner.
     * @return array
     */
    public function find(array $sources, array $exclude = [], array $extensions = ['php'])
    {
        $fileMasks = $this->turnExtensionsToMask($extensions);
        $files = [];
        foreach ($sources as $source) {
            $files = \array_merge($files, $this->getFilesFromSource($source, $exclude, $fileMasks));
        }
        return $files;
    }

    /**
     * Format un tableau de string vers un mask.
     *
     * @param array $extensions Tableau cotnenant les extensions acceptées.
     * @return string
     */
    private function turnExtensionsToMask(array $extensions)
    {
        \array_walk($extensions, function (&$value) {
            $value = '*.' . \preg_quote($value);
        });
        return \implode('|', $extensions);
    }

    /**
     * Retourne la liste des fichiers contenu dans la source.
     *
     * @param string $source    Chemin vers le dossier source.
     * @param array  $excludes  Liste des dossiers à ignorer.
     * @param string $fileMasks Mask des fichier à catcher.
     * @return array
     */
    private function getFilesFromSource($source, array $excludes, $fileMasks)
    {
        $finder = new BaseFinder();
        $finder->files()->ignoreUnreadableDirs()->in($source)->name($fileMasks);
        foreach ($excludes as $exclude) {
            $finder->exclude($exclude);
        }
        return iterator_to_array($finder);
    }
}
