<?php 

namespace App\Repositories;

use Illuminate\Database\Capsule\Manager as DB;
use App\Models\Content;

class ContentRepository {

    function insertContent(array $data): Content {
        return DB::transaction(function () use ($data) {
            $newContent = Content::create($data);
            return $newContent->fresh();
        });
    }

    function updateContentById(int $id, array $data): ?Content {
        return DB::transaction(function () use ($id, $data) {
            $content = Content::find($id);
    
            if (!$content) {
                throw new \Exception("El contenido con ID $id no existe.");
            }
    
            // Actualizar el contenido
            $content->update($data);
    
            // Retornar el modelo actualizado
            return $content->fresh();
        });
    }

    function updateContentOrder(Content $content, ?int $order = null): Content {
        return DB::transaction(function () use ($content, $order) {
            // Verificar si el modelo tiene 'order' y 'section_id' en su fillable
            if (!in_array('order', $content->getFillable()) || !in_array('section_id', $content->getFillable())) {
                throw new \Exception("El modelo no tiene 'order' o 'section_id' en su fillable.");
            }
    
            $section_id = $content->section_id; // Sección a la que pertenece el contenido
            $maxOrder = $content::where('section_id', $section_id)->max('order') ?? 0;
    
            if ($order === null) {
                $order = $maxOrder + 1; // Si no se envía orden, asignarlo al final de la sección
            } else {
                // Desplazar hacia abajo los contenidos dentro de la misma sección
                $content::where('section_id', $section_id)
                        ->where('order', '>=', $order)
                        ->where('id', '!=', $content->id)
                        ->increment('order');
            }
    
            // Actualizar el contenido con el nuevo orden
            $content->update(['order' => $order]);
    
            return $content->refresh(); // Retorna el modelo actualizado
        });
    }

    public function getContentById(int $id) : ?object  {
        return Content::with(['storage_file', 'meeting'])->where('id', $id)->first();
    }
}