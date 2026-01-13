<?php
include('../config/connection.php');

// Initial services data
$services = [
    [
        'service_name' => 'Serenity Tinting',
        'price' => 500.00,
        'description' => 'Add a touch of natural tint to your own lashes to create a deeper, fuller-looking lash without makeup. It is ideal for those who don\'t like fussing with makeup and want to make a statement without effort.'
    ],
    [
        'service_name' => 'Serenity Classic',
        'price' => 600.00,
        'description' => 'They create a beautifully natural look with our classic lashes. This style is a timeless, elegant look, by adding one lash extension to each of your natural lashes and giving them just the right amount of fullness.'
    ],
    [
        'service_name' => 'Serenity Hybrid',
        'price' => 800.00,
        'description' => 'The hybrid lashes offer a nice mixture of classic and volume techniques for a fuller-looking lash, using a combination of individual and fan lashes. If you\'re after a step up in classic lashes but with more depth and glamour added to the mix, this style is for you.'
    ],
    [
        'service_name' => 'Serenity Volume',
        'price' => 1000.00,
        'description' => 'The ultra fine extension used in the volume lashes give it a gorgeous, fluffy look. The result is a lush and full look that\'s as big and bold as you want it to be.'
    ],
    [
        'service_name' => 'Serenity Mega',
        'price' => 1200.00,
        'description' => 'The biggest in density and drama, Mega volume lashes. They offer immense volume and fullness with even more extensions per lash, giving you a high impact, glamorous effect that is perfect for special occasions or if you\'re just looking for a high volume, glamourous look.'
    ],
    [
        'service_name' => 'Serenity Removal',
        'price' => 200.00,
        'description' => 'It is a process of taking off artificial eyelashes, either extensions or false lashes, from the natural lash line. This is typically done to give the natural lashes a break, to change the style of the lashes, or to remove them due to irritation or damage.'
    ]
];

// Clear existing services
$conn->query("TRUNCATE TABLE services");

// Prepare and execute insert statements
foreach ($services as $service) {
    $stmt = $conn->prepare("INSERT INTO services (service_name, price, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $service['service_name'], $service['price'], $service['description']);
    
    if ($stmt->execute()) {
        echo "Added service: {$service['service_name']}<br>";
    } else {
        echo "Error adding service: {$service['service_name']}<br>";
    }
}

echo "<br>Services populated successfully!";
?>