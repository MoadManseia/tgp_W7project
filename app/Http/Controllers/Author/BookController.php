<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        return User::findOrFail($user->id)->load('books');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $inputs = $request->validate([
            'title' => ['required','max:255'],
            'publish_year' => ['required','min:4','max:4'],
            'price' => ['required','decimal:1,50'],
            'isbn' => ['required'],
            'category_id' => ['required','exists:categories,id'],
        ]);

        $user = Auth::user();
        $book = Book::create($inputs);
        $user->books()->attach($book->id);

        return $book;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
