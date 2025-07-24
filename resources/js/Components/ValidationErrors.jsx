export default function ValidationErrors({ errors = [], className = '' }) {
    return (
        <div className={`${className}`}>
            {Object.keys(errors).length > 0 && (
                <div className="mb-4">
                    <div className="font-medium text-red-600">
                        Oops! Algo deu errado.
                    </div>

                    <ul className="mt-3 list-disc list-inside text-sm text-red-600">
                        {Object.keys(errors).map((key, index) => (
                            <li key={index}>{errors[key]}</li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}