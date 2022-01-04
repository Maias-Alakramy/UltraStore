<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Like;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sortBy');
        $category = $request->query('category');
        $productsQuary = DB::table('Products');
        if($category)
            $category_id = Category::firstOrCreate(['name' => $input['category_name']])->id;
            $productsQuary->where('category_id', $category_id);
        if($sortBy)
            $productsQuary->orderBy($sortBy, 'asc');
        $products = $productsQuary->get();

        return response()->json($products, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $input = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'category_name' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'exp_date' => 'required|date',
                'quantity' => 'required|numeric',
                'days1' => 'required|numeric',
                'discount1' => 'required|numeric',
                'days2' => 'required|numeric|max:days1',
                'discount2' => 'required|numeric',
                'days3' => 'required|numeric|max:days2',
                'discount3' => 'required|numeric'
            ]);
        }catch(ValidationException $e){
            return response()->json(['message'=>$e->getMessage()],400);
        }
        $imageName = date('YmdHis').'_'.$input['name'].'.'.$request->image->extension();

        $category_id = Category::firstOrCreate(['name' => $input['category_name']])->id;

        $product = Product::create([
            "name" => $input['name'],
            "price" => $input['price'],
            "category_id" => $category_id,
            "user_id" => auth()->user()->id,
            "contact_info" => auth()->user()->contact_number,
            "exp_date" => $input['exp_date'],
            "quantity" => $input['quantity'],
            "days1" => $input['days1'],
            "discount1" => $input['discount1'],
            "days2" => $input['days2'],
            "discount2" => $input['discount2'],
            "days3" => $input['days3'],
            "discount3" => $input['discount3'],
            "img_url" => $imageName
        ]);

        $request->image->move(public_path('images'), $imageName);

        return response()->json(["message"=>"Product added successuflly"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $product = Product::findOrFail($id);
            $product['likes'] = $product->like()->count();
            $product['final_price'] = finalPrice($product->id);
            return response()->json($product, 200);
        }catch(ModelNotFoundException $e){
            return response()->json(["message"=>"Not Found"], 404);
        }catch(Exception $e){
            return response()->json(["message"=>"Internal Server Error"], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $input = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'category_name' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'quantity' => 'required|numeric',
                'days1' => 'required|numeric',
                'discount1' => 'required|numeric',
                'days2' => 'required|numeric|max:days1',
                'discount2' => 'required|numeric',
                'days3' => 'required|numeric|max:days2',
                'discount3' => 'required|numeric'
            ]);
            
            $imageName = date('YmdHis').'_'.$input['name'].'.'.$request->image->extension();
            
            $category_id = Category::firstOrCreate(['name' => $input['category_name']])->id;

            $product = Product::findorfail($id);
            if($product->user_id == auth()->user()->id){
                File::delete($product->img_url);
                $product->update([
                    "name" => $input['name'],
                    "price" => $input['price'],
                    "category_id" => $category_id,
                    "quantity" => $input['quantity'],
                    "days1" => $input['days1'],
                    "discount1" => $input['discount1'],
                    "days2" => $input['days2'],
                    "discount2" => $input['discount2'],
                    "days3" => $input['days3'],
                    "discount3" => $input['discount3'],
                    "img_url" => $imageName
                ]);
                $request->image->move(public_path('images'), $imageName);
                return response()->json(["message"=>"Product updated successuflly"],200);
            }
            else{
                return response()->json(["message"=>"Forbidden"],403);
            }
        }catch(ModelNotFoundException $e){
            return response()->json(["message"=>"Not Found"],404);
        }catch(ValidationException $e){
            return response()->json(["message"=>$e->getMessage()],400);
        }catch(Exception $e){
            return response()->json(["message"=>"Internal Server Error"],500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $product = Product::findorfail($id);
            if($product->user_id == auth()->user()->id){
                File::delete($product->img_url);
                $product->delete();
                return response()->json(["message"=>"Product deleted successuflly"],200);
            }
            else{
                return response()->json(["message"=>"Forbidden"],403);
            }
        }catch(ModelNotFoundException $e){
            return response()->json(["message"=>"Not Found"],404);
        }catch(Exception $e){
            return response()->json(["message"=>"Internal Server Error"], 500);
        }   
    }

    public function like($id)
    {
        try{
            $likes = Like::where(['product_id' => $id,'user_id'=>auth()->user()->id])->count();
            if($likes == 0){
                Like::create([
                    'product_id' => $id,
                    'user_id' => auth()->user()->id
                ]);
                return response()->json(["message"=>"Product liked successuflly"],200);
            }
            else{
                return response()->json(["message"=>"This user liked this product before"],403);
            }
        }catch(ModelNotFoundException $e){
            return response()->json(["message"=>"Not Found"],404);
        }catch(Exception $e){
            return response()->json(["message"=>"Internal Server Error"], 500);
        }
    }

    public function deleteondate(){
        $products=Product::where('expiration_date','<',Carbon::now())->delete();
    }

    public function finalPrice($id){

        $today = Carbon::now();
        $product = Product::find($id);
        $expiration_date=$product->expiration_date;
        $price=$product->value('price');
        $day1=$product->day1;
        $day2=$product->day2;
        $day3=$product->day3;
        $disacount1=$product->disacount1;
        $disacount2=$product->disacount2;
        $disacount3=$product->disacount3;
        $newprice=$price;
        $result = $today->diffInDays($expiration_date);

        if($result > $day3) {
            $newprice=$price - $price*$disacount3/100;
            $price=$newprice;

        }else{
            if($result > $day2 ) {
                $newprice=$price - $price*$disacount2/100;
                $price=$newprice;

            }else{
                if($result > $day1 )
                    $newprice=$price - $price*$disacount1/100;
                $price=$newprice;


            }
        }
        return $price;
    }
}
