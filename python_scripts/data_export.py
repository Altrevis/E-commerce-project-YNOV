#!/usr/bin/env python3
import mysql.connector
import csv
import json
from datetime import datetime

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host='localhost',
        database='ecommerce-ynov',
        user='leo',
        password='leo'
    )

# Export products to CSV
def export_products_csv():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM produits")
    products = cursor.fetchall()

    with open('products_export.csv', 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['id_produit', 'nom_produit', 'description', 'prix', 'stock', 'id_categorie', 'id_fournisseur']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        for product in products:
            writer.writerow(product)

    cursor.close()
    conn.close()
    print("Products exported to products_export.csv")

# Export orders to JSON
def export_orders_json():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT c.*, cl.prenom, cl.nom, cl.email
        FROM commandes c
        JOIN clients cl ON c.id_client = cl.id_client
        ORDER BY c.date_commande DESC
    """)
    orders = cursor.fetchall()

    for order in orders:
        order['date_commande'] = str(order['date_commande'])

    with open('orders_export.json', 'w', encoding='utf-8') as jsonfile:
        json.dump(orders, jsonfile, indent=2, ensure_ascii=False)

    cursor.close()
    conn.close()
    print("Orders exported to orders_export.json")

# Generate sales report
def generate_sales_report():
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT p.nom_produit, SUM(lc.quantite) as total_quantity, SUM(lc.prix_unitaire * lc.quantite) as total_revenue
        FROM lignes_commandes lc
        JOIN produits p ON lc.id_produit = p.id_produit
        GROUP BY lc.id_produit
        ORDER BY total_revenue DESC
    """)
    sales = cursor.fetchall()

    with open('sales_report.csv', 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['Product Name', 'Total Quantity Sold', 'Total Revenue']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        for sale in sales:
            writer.writerow({
                'Product Name': sale['nom_produit'],
                'Total Quantity Sold': sale['total_quantity'],
                'Total Revenue': f"{sale['total_revenue']:.2f} €"
            })

    cursor.close()
    conn.close()
    print("Sales report generated: sales_report.csv")

if __name__ == "__main__":
    export_products_csv()
    export_orders_json()
    generate_sales_report()
    print("All exports completed!")
