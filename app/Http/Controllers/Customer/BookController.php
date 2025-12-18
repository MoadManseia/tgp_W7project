<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $booksQuery = Book::query();

        if($request->has('cat'))
        {
            $booksQuery->where('category_id',$request->cat);
        }

        return $booksQuery->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $inputs = $request->validate([
            'title' => ['required', 'max:255'],
            'publish_year' => ['required', 'min:4', 'max:4'],
            'price' => ['required', 'decimal:1,50'],
            'isbn' => ['required', 'unique:books,isbn'],
            'category_id' => ['required', 'exists:categories,id'],
        ]);

        $book = Book::create($inputs);

        return response()->json($book, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = Book::findOrFail($id);
        return response()->json($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $book = Book::findOrFail($id);

        $inputs = $request->validate([
            'title' => ['sometimes', 'required', 'max:255'],
            'publish_year' => ['sometimes', 'required', 'min:4', 'max:4'],
            'price' => ['sometimes', 'required', 'decimal:1,50'],
            'isbn' => ['sometimes', 'required', 'unique:books,isbn,' . $id],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
        ]);

        $book->update($inputs);

        return response()->json($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
