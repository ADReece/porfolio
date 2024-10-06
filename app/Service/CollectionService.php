<?php
namespace App\Service;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Model;

class CollectionService
{
    public Collection | null $collection = null;
    public function __construct(Collection | null $collection = null)
    {
        $this->collection = !is_null($collection) ? $collection : new Collection();
        $this->collection->load(['sets', 'sets.photos', 'user']);
    }

    public function createSet(string $name) : Model
    {
        return $this->collection->sets()->create([
            'name' => $name
        ]);
    }

}