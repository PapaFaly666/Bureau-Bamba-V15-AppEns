<?php

namespace App\Http\Controllers;

use App\Models\ApprenantFirebaseModel;
use App\Models\User;
use Illuminate\Http\Request;

class ApprenantController extends Controller
{
    protected $apprenantModel;

    public function __construct(ApprenantFirebaseModel $apprenantModel)
    {
        $this->apprenantModel = $apprenantModel;
    }

    public function filterByReferentiel(Request $request, $referentielId)
    {
        try {
            $apprenants = $this->apprenantModel->filterByReferentiel($referentielId);
            if (empty($apprenants)) {
                return response()->json(['message' => 'Aucun apprenant trouvé pour ce référentiel'], 200);
            }
            return response()->json($apprenants);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $apprenant = $this->apprenantModel->getApprenantWithReferentiels($id);
            return response()->json($apprenant);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getInactiveApprenantsFromActivePromotion()
    {
        try {
            $apprenants = $this->apprenantModel->getApprenantsFromActivePromotion();
            $inactiveApprenants = [];

            foreach ($apprenants as $apprenant) {
                $user = User::find($apprenant['user_id']);
                if ($user && $user->statut === 'Inactif') {
                    $inactiveApprenants[] = $apprenant;
                }
            }

            if (empty($inactiveApprenants)) {
                return response()->json(['message' => 'Aucun apprenant inactif trouvé dans la promotion active'], 200);
            }
            return response()->json($inactiveApprenants);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
