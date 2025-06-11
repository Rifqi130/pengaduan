const { Client } = require("pg");

const client = new Client({
  host: "localhost",
  user: "postgres", // ganti sesuai user PostgreSQL Anda
  password: "password", // ganti sesuai password PostgreSQL Anda
  database: "nama_database", // ganti sesuai nama database Anda
  port: 5432,
});

client
  .connect()
  .then(() => {
    console.log("Koneksi ke PostgreSQL berhasil!");
    return client.end();
  })
  .catch((err) => {
    console.error("Koneksi ke PostgreSQL gagal:", err.message);
  });
