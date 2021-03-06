<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

use App\Category;
use App\Product;
use App\Tag;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        return view('admin.sections.dashboard');
    }
    public function index()
    {
        $products = Product::orderBy('id', 'DESC')
            ->where('user_id', auth()->user()->id)
            ->paginate(4);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->pluck('name', 'id');
        $tags       = Tag::orderBy('name', 'ASC')->get();
        $image = null;
        return view('admin.products.create', compact('categories', 'tags', 'image'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductStoreRequest $request)
    {
        $notification = array(
            'message' => 'Post creado con éxito!', 
            'alert-type' => 'success'
        );

        $product = Product::create($request->all());
        $this->authorize('pass', $product);

        //IMAGE 
        if($request->file('image')){
            $path = Storage::disk('public')->put('images',  $request->file('image'));
            $product->fill(['file' => asset($path)])->save();
        }

        //TAGS
        $product->tags()->attach($request->get('tags'));

        return redirect()->route('products.edit', $product->id)->with($notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        $this->authorize('pass', $product);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $image = null;
        $categories = Category::orderBy('name', 'ASC')->pluck('name', 'id');
        $tags       = Tag::orderBy('name', 'ASC')->get();
        $product    = Product::find($id);
        $this->authorize('pass', $product);

        return view('admin.products.edit', compact('product', 'categories', 'tags', 'image'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductUpdateRequest $request, $id)
    {
        $notification = array(
            'message' => 'Post actualizado con éxito!', 
            'alert-type' => 'warning'
        );

        $product = Product::find($id);
        $this->authorize('pass', $product);

        $product->fill($request->all())->save();

        //IMAGE 
        if($request->file('image')){
            $path = Storage::disk('public')->put('images',  $request->file('image'));
            $product->fill(['file' => asset($path)])->save();
        }

        //TAGS
        $product->tags()->sync($request->get('tags'));

        return redirect()->route('products.edit', $product->id)->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notification = array(
            'message' => 'Post eliminado con éxito!', 
            'alert-type' => 'error'
        );
        $product = Product::find($id)->delete();
        //$this->authorize('pass', $product);

        return back()->with($notification);
    }   
}
