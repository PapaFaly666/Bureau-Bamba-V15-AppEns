<?php

namespace App\Models;

use Kreait\Firebase\Contract\Firestore;

class ApprenantFirebaseModel implements FirebaseModelInterface
{
    protected $firestore;
    protected $collection = 'apprenants';

    public function __construct(Firestore $firestore)
    {
        $this->firestore = $firestore->database();
    }

    public function create(array $data)
    {
        // Implémentez la méthode create selon vos besoins
    }

    public function update(string $id, array $data)
    {
        // Implémentez la méthode update selon vos besoins
    }

    public function delete(string $id)
    {
        // Implémentez la méthode delete selon vos besoins
    }

    public function find(string $id)
    {
        return $this->firestore->collection($this->collection)->document($id)->snapshot();
    }

    public function findByField(string $field, $value)
    {
        // Implémentez la méthode findByField selon vos besoins
    }

    public function getApprenantWithReferentiels(string $id)
    {
        $apprenant = $this->find($id);
        if (!$apprenant->exists()) {
            throw new \Exception('Apprenant non trouvé');
        }

        $apprenantData = $apprenant->data();
        $referentiels = [];

        if (isset($apprenantData['referentiels'])) {
            foreach ($apprenantData['referentiels'] as $referentiel) {
                $referentielData = $this->firestore->collection('referentiels')->document($referentiel['id'])->snapshot()->data();
                $referentiels[] = $referentielData;
            }
        }

        $apprenantData['referentiels'] = $referentiels;
        return array_merge(['id' => $apprenant->id()], $apprenantData);
    }

    public function filterByReferentiel(string $referentielId)
    {
        $apprenants = $this->firestore->collection($this->collection)->documents();
        $filteredApprenants = [];

        foreach ($apprenants as $apprenant) {
            $apprenantData = $apprenant->data();
            if (isset($apprenantData['referentiels'])) {
                foreach ($apprenantData['referentiels'] as $referentiel) {
                    if ($referentiel['id'] === $referentielId) {
                        $filteredApprenants[] = array_merge(['id' => $apprenant->id()], $apprenantData);
                        break;
                    }
                }
            }
        }

        return $filteredApprenants;
    }

    public function getApprenantsFromActivePromotion()
    {
        // Obtenir la promotion active
        $promotions = $this->firestore->collection('promotions')
            ->where('etat', '=', 'Actif')
            ->documents();

        $activePromotion = null;
        foreach ($promotions as $promotion) {
            $activePromotion = $promotion;
            break;
        }

        if (!$activePromotion) {
            throw new \Exception('Aucune promotion active trouvée');
        }

        $activePromotionData = $activePromotion->data();
        $apprenants = [];

        if (isset($activePromotionData['apprenants'])) {
            foreach ($activePromotionData['apprenants'] as $apprenantId) {
                $apprenant = $this->find($apprenantId);
                if ($apprenant->exists()) {
                    $apprenants[] = array_merge(['id' => $apprenant->id()], $apprenant->data());
                }
            }
        }

        return $apprenants;
    }
}
