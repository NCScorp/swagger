

export const ErrorPage = {

    render: function(customMessage: string = 'Oops! Parece que encontramos um problema por aqui...',  url: string = "") {

        return (`
            <div style="margin:0;padding:0;background-color:#FAFAFA;font-family: sans-serif;">
                <div style="background-color:#0F4E71;">
                    <img src="https://s3-sa-east-1.amazonaws.com/cdn.nasajon/wp-content/uploads/2018/06/12150333/logo-nasajon_positivo.png" alt="">
                </div>
                <div style="padding:10% 10px 10px;">
                    <img style="width:100px;max-width:100px;margin:10px auto;display:block;" src="https://images-na.ssl-images-amazon.com/images/I/51PxvVVOjVL._AC_SY355_.jpg" alt="">
                    <div style="margin:0 auto;width:100%;max-width:1000px; border:1px solid #DDDDDD;text-align:center;border-radius:3px;padding:10px 0 30px;">
                        <h1 style="color:#0F4E71;">${customMessage}</h1>
                        <a href="${url}" style="border:0;background-color:#0F4E71;color:#fff;padding:10px;border-radius:3px;cursor:pointer;">Voltar</a>
                    </div>
                </div>
            </div>
        `)
    }
}
