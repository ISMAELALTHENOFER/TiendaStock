export function Table({ children, className = '' }) {
    return <div className="table-region"><table className={`w-full min-w-[38rem] border-collapse text-left text-sm ${className}`}>{children}</table></div>;
}
