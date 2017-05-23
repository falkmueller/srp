(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD
    define(['exports'], factory);
  } else if (typeof exports === 'object' && typeof exports.nodeName !== 'string') {
    // Node, CommonJS-like
    factory(module.exports);
  } else {
    factory(root);
  }
})(this, function (exports) {

    var srp = function(){
        this.n_base64 = "dadfccb918e5f651d7a1b851efab43f2c17068c69013e37033347e8da75ca8d8370c26c4fbf1a4aaa4afd9b5ab32343749ee4fbf6fa279856fd7c3ade30ecf2b";
        this.g = "2";
        this.hash_alg = "sha256";
        this.k = this.hash(this.n_base64 + this.g);
        this.rand_length = 128;
    }

    srp.prototype.generateX = function(s, username, password){
            var s = this.base2BigInt(s);
            s = this.bigIntToStr(s);
            var x = this.hash(s + this.hash(username + ":" + password));

            return x;
        }

     srp.prototype.generateV = function(x){
         var g = BigInt.str2bigInt(this.g, 10);
         var n = this.base2BigInt(this.n_base64);
         var x = this.base2BigInt(x);
         var v = this.bigIntToBase(BigInt.powMod(g, x, n));

         return v;
     }   

     srp.prototype.generateA = function(a){
         var g = BigInt.str2bigInt(this.g, 10);
         var n = this.base2BigInt(this.n_base64);
         var a = this.base2BigInt(a);

         var A = this.bigIntToBase(BigInt.powMod(g, a, n));

         return A;
     }

     srp.prototype.generateS_Client = function(A, B, a, x){
         var u = this.base2BigInt(this.generateU(A, B));
         var B = this.base2BigInt(B);
         var a = this.base2BigInt(a);
         var k = this.base2BigInt(this.k);
         var g = BigInt.str2bigInt(this.g, 10);
         var n = this.base2BigInt(this.n_base64);
         var x = this.base2BigInt(x);

         var S = this.bigIntToBase(BigInt.powMod(BigInt.sub(B, BigInt.mult(k, BigInt.powMod(g,x,n))),BigInt.add(a, BigInt.mult(u, x)), n));

         return S;
     }


      srp.prototype.generateB = function(b, v){
          var n = this.base2BigInt(this.n_base64);
          var v = this.base2BigInt(v);
          var b = this.base2BigInt(b);
          var k = this.base2BigInt(this.k);
          var g = BigInt.str2bigInt(this.g, 10);

          var B = this.bigIntToBase(BigInt.add(BigInt.mult(k,v), BigInt.powMod(g,b,n)));

          return B;
      }

    srp.prototype.generateS_Server = function(A,B,b,v){
        var u = this.base2BigInt(this.generateU(A, B));
        var n = this.base2BigInt(this.n_base64);
        var A = this.base2BigInt(A);
        var v = this.base2BigInt(v);
        var b = this.base2BigInt(b);

        var S = this.bigIntToBase(BigInt.powMod(BigInt.mult(A, BigInt.powMod(v,u,n)),b,n));

        return S;
    }

    srp.prototype.getRandomSeed = function(length){
        length = length ||this.rand_length;

        return this.bigIntToBase(BigInt.randBigInt(length * 4))
    }

    srp.prototype.generateU = function(A, B){
        return this.hash(A + B);
    }

    srp.prototype.generateM1 = function(A, B, S){
        return this.hash(A + B + S);
    }


    srp.prototype.generateM2 = function(A, M1, S){
        return this.hash(A + M1 + S);
    }

    srp.prototype.generateK = function(S){
        return this.hash(S);
    }

    srp.prototype.hash = function(value){
        if(this.hash_alg == "sha256"){
            return sha256(sha256(value));
        }

        throw "hash algorithm not supported";
        return null;
    }

    srp.prototype.base2BigInt = function(value, base){
        base = base || 16;
        return BigInt.str2bigInt(value, base);
    }

    srp.prototype.bigIntToStr = function(value){
        return BigInt.bigInt2str(value, 10)
    }

    srp.prototype.bigIntToBase = function(value, base){
        base = base || 16;
        return BigInt.bigInt2str(value, base).toLowerCase();
    }

    exports.srp = srp;
});

